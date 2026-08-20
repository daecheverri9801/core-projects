<?php

namespace App\Services;

use App\Models\Apartamento;
use App\Models\Local;
use App\Models\Proyecto;
use App\Models\TipoApartamento;
use App\Models\Venta;

class PriceEngine
{
    /* ============================================================
     * 1. Calcular bloque actual según ventas activas del proyecto
     * ============================================================ */
    public function obtenerBloqueActual(Proyecto $proyecto): int
    {
        $ventas = Venta::where('id_proyecto', $proyecto->id_proyecto)
            ->whereIn('tipo_operacion', ['venta', 'separacion'])
            ->whereNotNull('id_apartamento')
            ->count();

        $politicasVentas = $proyecto->politicasPrecio()
            ->where('estado', true)
            ->whereNotNull('ventas_por_escalon')
            ->orderBy('id_politica_precio', 'asc')
            ->get();

        $bloque = 0;
        $acumulado = 0;

        foreach ($politicasVentas as $p) {
            $ventasEscalon = (int) ($p->ventas_por_escalon ?? 0);

            if ($ventasEscalon <= 0) {
                continue;
            }

            $acumulado += $ventasEscalon;

            if ($ventas >= $acumulado) {
                $bloque++;
            } else {
                break;
            }
        }

        return $bloque;
    }

    /* ============================================================
     * 2. Calcular multiplicador acumulado de políticas
     * ============================================================ */
    public function calcularFactorAumento(
        Proyecto $proyecto,
        int $bloqueActual
    ): float {
        $politicas = $proyecto->politicasPrecio()
            ->where('estado', true)
            ->orderBy('id_politica_precio', 'asc')
            ->get();

        $politicasVentas = $politicas
            ->whereNotNull('ventas_por_escalon')
            ->values();

        $idsPorVentas = [];

        for ($i = 0; $i < $bloqueActual; $i++) {
            if (!isset($politicasVentas[$i])) {
                break;
            }

            $idsPorVentas[(int) $politicasVentas[$i]->id_politica_precio] = true;
        }

        $hoy = now()->startOfDay();

        $factor = 1.0;

        foreach ($politicas as $p) {
            $id = (int) $p->id_politica_precio;

            $aplicaPorVentas = isset($idsPorVentas[$id]);

            $aplicaPorFecha =
                !is_null($p->aplica_desde)
                && $p->aplica_desde->copy()->startOfDay()->lte($hoy);

            if ($aplicaPorVentas || $aplicaPorFecha) {
                $aumento = (float) $p->porcentaje_aumento;

                $factor *= 1.0 + ($aumento / 100.0);
            }
        }

        return $factor;
    }

    /* ============================================================
     * 3. Calcular prima de altura de un apartamento
     * ============================================================ */
    public function calcularPrimaAltura(
        Apartamento $apartamento
    ): float {
        $apartamento->loadMissing([
            'tipoApartamento',
            'pisoTorre',
            'torre.proyecto',
        ]);

        $tipo = $apartamento->tipoApartamento;
        $piso = $apartamento->pisoTorre;
        $torre = $apartamento->torre;

        if (!$tipo || !$piso || !$torre) {
            return 0.0;
        }

        /*
         * Protección de integridad:
         * el piso necesariamente debe pertenecer a la torre
         * del apartamento.
         */
        if ((int) $piso->id_torre !== (int) $torre->id_torre) {
            return 0.0;
        }

        /*
         * ========================================================
         * NUEVA CONFIGURACIÓN
         * ========================================================
         *
         * NULL = tipo antiguo todavía no migrado.
         *
         * true / false = el tipo ya utiliza la configuración nueva.
         */
        if ($tipo->prima_altura_activa !== null) {
            if (!$tipo->prima_altura_activa) {
                return 0.0;
            }

            return $this->calcularPrimaPorParametros(
                (int) $piso->nivel,
                $tipo->nivel_inicio_prima,
                $tipo->prima_altura_base,
                $tipo->prima_altura_incremento
            );
        }

        /*
         * ========================================================
         * COMPATIBILIDAD TEMPORAL CON CONFIGURACIÓN ANTIGUA
         * ========================================================
         *
         * Se eliminará cuando todos los tipos existentes hayan
         * sido configurados con la nueva prima.
         */
        $proyecto = $torre->proyecto;

        if (
            !$proyecto
            || !$proyecto->prima_altura_activa
        ) {
            return 0.0;
        }

        return $this->calcularPrimaPorParametros(
            (int) $piso->nivel,
            $torre->nivel_inicio_prima,
            $proyecto->prima_altura_base,
            $proyecto->prima_altura_incremento
        );
    }

    /* ============================================================
     * 4. Fórmula base de prima de altura
     * ============================================================ */
    private function calcularPrimaPorParametros(
        int $nivelActual,
        $nivelInicio,
        $primaBase,
        $incremento
    ): float {
        $nivelInicio = (int) ($nivelInicio ?? 0);

        if ($nivelInicio <= 0) {
            return 0.0;
        }

        if ($nivelActual < $nivelInicio) {
            return 0.0;
        }

        $primaBase = (float) ($primaBase ?? 0);
        $incremento = (float) ($incremento ?? 0);

        $cantidadIncrementos = $nivelActual - $nivelInicio;

        return round(
            $primaBase + ($cantidadIncrementos * $incremento),
            2
        );
    }

    /* ============================================================
     * 5. Recalcular todos los inmuebles disponibles del proyecto
     * ============================================================ */
    public function recalcularProyecto(
        Proyecto $proyecto
    ): void {
        $bloque = $this->obtenerBloqueActual($proyecto);

        $factor = $this->calcularFactorAumento(
            $proyecto,
            $bloque
        );

        /*
         * Apartamentos disponibles
         */
        $apartamentos = Apartamento::where(
            'id_estado_inmueble',
            function ($q) {
                $q->select('id_estado_inmueble')
                    ->from('estados_inmueble')
                    ->where('nombre', 'Disponible')
                    ->limit(1);
            }
        )
            ->whereHas(
                'torre',
                fn($q) => $q->where(
                    'id_proyecto',
                    $proyecto->id_proyecto
                )
            )
            ->with([
                'tipoApartamento',
                'pisoTorre',
                'torre.proyecto',
            ])
            ->get();

        foreach ($apartamentos as $apartamento) {
            $this->recalcularInmueble(
                $apartamento,
                $factor,
                false
            );
        }

        /*
         * Locales disponibles.
         *
         * La nueva prima por TipoApartamento NO aplica a locales.
         */
        $locales = Local::where(
            'id_estado_inmueble',
            function ($q) {
                $q->select('id_estado_inmueble')
                    ->from('estados_inmueble')
                    ->where('nombre', 'Disponible')
                    ->limit(1);
            }
        )
            ->whereHas(
                'torre',
                fn($q) => $q->where(
                    'id_proyecto',
                    $proyecto->id_proyecto
                )
            )
            ->get();

        foreach ($locales as $local) {
            $this->recalcularInmueble(
                $local,
                $factor,
                true
            );
        }
    }

    /* ============================================================
     * 6. Recalcular apartamentos de un TipoApartamento
     * ============================================================ */
    public function recalcularTipoApartamento(
        TipoApartamento $tipo
    ): void {
        $tipo->loadMissing('proyecto');

        $proyecto = $tipo->proyecto;

        if (!$proyecto) {
            return;
        }

        $bloque = $this->obtenerBloqueActual($proyecto);

        $factor = $this->calcularFactorAumento(
            $proyecto,
            $bloque
        );

        /*
         * Solamente modificamos inventario disponible.
         *
         * Un apartamento vendido o separado conserva el precio
         * comercial con el que fue gestionada la operación.
         */
        $apartamentos = $tipo->apartamentos()
            ->where(
                'id_estado_inmueble',
                function ($q) {
                    $q->select('id_estado_inmueble')
                        ->from('estados_inmueble')
                        ->where('nombre', 'Disponible')
                        ->limit(1);
                }
            )
            ->with([
                'tipoApartamento',
                'pisoTorre',
                'torre.proyecto',
            ])
            ->get();

        foreach ($apartamentos as $apartamento) {
            /*
             * El valor_total del apartamento representa la base
             * inmutable actual proveniente del TipoApartamento.
             */
            $apartamento->valor_total =
                (float) ($tipo->valor_estimado ?? 0);

            $apartamento->saveQuietly();

            $this->recalcularInmueble(
                $apartamento,
                $factor,
                false
            );
        }
    }

    /* ============================================================
     * 7. Recalcular un inmueble
     * ============================================================ */
    public function recalcularInmueble(
        $inmueble,
        float $factor,
        bool $esLocal = false
    ): void {
        /*
         * ========================================================
         * BASE
         * ========================================================
         */
        if ($esLocal) {
            $valorBase =
                (float) ($inmueble->valor_m2 ?? 0)
                * (float) ($inmueble->area_total_local ?? 0);

            /*
             * Conservamos cualquier comportamiento previo de locales.
             * La nueva regla de TipoApartamento no interviene aquí.
             */
            $primaAltura =
                (float) ($inmueble->prima_altura ?? 0);
        } else {
            if (!$inmueble instanceof Apartamento) {
                return;
            }

            $valorBase =
                (float) ($inmueble->valor_total ?? 0);

            $primaAltura =
                $this->calcularPrimaAltura($inmueble);
        }

        /*
         * ========================================================
         * POLÍTICA
         * ========================================================
         *
         * La política se calcula SOLAMENTE sobre el valor base.
         * La prima de altura se suma posteriormente.
         */
        $valorPolitica =
            ($valorBase * $factor) - $valorBase;

        /*
         * ========================================================
         * VALOR FINAL
         * ========================================================
         *
         * valor_final
         * =
         * valor_base
         * + valor_politica
         * + prima_altura
         */
        $valorFinal =
            $valorBase
            + $valorPolitica
            + $primaAltura;

        $data = [
            'valor_politica' => round($valorPolitica),
            'valor_final' => round($valorFinal),
        ];

        if (!$esLocal) {
            $data['prima_altura'] = round($primaAltura);
        }

        /*
         * saveQuietly evita disparar nuevamente
         * ApartamentoObserver durante un recálculo interno.
         */
        $inmueble->fill($data);
        $inmueble->saveQuietly();
    }

    /* ============================================================
     * 8. Recalcular apartamento según políticas activas
     * ============================================================ */
    public function recalcularApartamentoSegunPoliticasActivas(
        Apartamento $apartamento
    ): void {
        $apartamento->loadMissing([
            'torre.proyecto',
            'tipoApartamento',
            'pisoTorre',
        ]);

        $proyecto =
            $apartamento->torre?->proyecto;

        if (!$proyecto) {
            return;
        }

        $bloque =
            $this->obtenerBloqueActual($proyecto);

        $factor =
            $this->calcularFactorAumento(
                $proyecto,
                $bloque
            );

        $this->recalcularInmueble(
            $apartamento,
            $factor,
            false
        );
    }

    /* ============================================================
     * 9. Llamado principal desde Ventas
     * ============================================================ */
    public function recalcularProyectoPorVenta(
        Venta $venta
    ): void {
        $proyecto =
            Proyecto::find($venta->id_proyecto);

        if (!$proyecto) {
            return;
        }

        $this->recalcularProyecto($proyecto);
    }
}
