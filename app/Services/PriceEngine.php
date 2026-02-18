<?php

namespace App\Services;

use App\Models\Proyecto;
use App\Models\Apartamento;
use App\Models\Local;
use App\Models\Venta;

use Illuminate\Support\Facades\DB;

class PriceEngine
{
    /* ============================================================
     * 1. Calcular bloque actual según ventas activas del proyecto
     * ============================================================ */
    public function obtenerBloqueActual(Proyecto $proyecto): int
    {
        $ventas = Venta::where('id_proyecto', $proyecto->id_proyecto)->count();

        $politicasVentas = $proyecto->politicasPrecio()
            ->where('estado', true)
            ->whereNotNull('ventas_por_escalon')
            ->orderBy('id_politica_precio', 'asc')
            ->get();

        $bloque = 0;
        $acumulado = 0;

        foreach ($politicasVentas as $p) {
            $ventasEscalon = (int) ($p->ventas_por_escalon ?? 0);
            if ($ventasEscalon <= 0) continue;

            $acumulado += $ventasEscalon;

            if ($ventas >= $acumulado) {
                $bloque++;
            } else {
                break;
            }
        }

        return $bloque; // 0,1,2...
    }

    /* ============================================================
     * 2. Calcular el multiplicador de precios acumulado
     * ============================================================ */
    public function calcularFactorAumento(Proyecto $proyecto, int $bloqueActual): float
    {
        $politicas = $proyecto->politicasPrecio()
            ->where('estado', true)
            ->orderBy('id_politica_precio', 'asc')
            ->get();

        // Políticas que aplican por ventas: las primeras N dentro de las que tienen ventas_por_escalon
        $politicasVentas = $politicas->whereNotNull('ventas_por_escalon')->values();
        $idsPorVentas = [];

        for ($i = 0; $i < $bloqueActual; $i++) {
            if (!isset($politicasVentas[$i])) break;
            $idsPorVentas[(int) $politicasVentas[$i]->id_politica_precio] = true;
        }

        $hoy = now()->startOfDay();
        $factor = 1.0;

        foreach ($politicas as $p) {
            $id = (int) $p->id_politica_precio;

            $aplicaPorVentas = isset($idsPorVentas[$id]);
            $aplicaPorFecha  = !is_null($p->aplica_desde) && $p->aplica_desde->startOfDay()->lte($hoy);

            // OR explícito
            if ($aplicaPorVentas || $aplicaPorFecha) {
                $aumento = (float) $p->porcentaje_aumento;
                $factor *= (1.0 + ($aumento / 100.0));
            }
        }

        return $factor;
    }

    /* ============================================================
     * 3. Recalcular todos los inmuebles disponibles del proyecto
     * ============================================================ */
    public function recalcularProyecto(Proyecto $proyecto): void
    {
        $bloque = $this->obtenerBloqueActual($proyecto);
        $factor = $this->calcularFactorAumento($proyecto, $bloque);

        // Apartamentos disponibles
        $apartamentos = Apartamento::where('id_estado_inmueble', function ($q) {
            $q->select('id_estado_inmueble')
                ->from('estados_inmueble')
                ->where('nombre', 'Disponible')
                ->limit(1);
        })
            ->whereHas('torre', fn($q) => $q->where('id_proyecto', $proyecto->id_proyecto))
            ->get();

        foreach ($apartamentos as $apto) {
            $this->recalcularInmueble($apto, $factor);
        }

        // Locales disponibles
        $locales = Local::where('id_estado_inmueble', function ($q) {
            $q->select('id_estado_inmueble')
                ->from('estados_inmueble')
                ->where('nombre', 'Disponible')
                ->limit(1);
        })
            ->whereHas('torre', fn($q) => $q->where('id_proyecto', $proyecto->id_proyecto))
            ->get();

        foreach ($locales as $local) {
            $this->recalcularInmueble($local, $factor, true);
        }
    }

    /* ============================================================
     * 4. Recalcular UN solo inmueble
     * ============================================================ */
    public function recalcularInmueble($inmueble, $factor, $esLocal = false)
    {
        if (!$esLocal) {
            // BASE INMUTABLE REAL
            $precioBase = (float) ($inmueble->valor_total ?? 0);
        } else {
            $precioBase = (float) (($inmueble->valor_m2 ?? 0) * ($inmueble->area_total_local ?? 0));
        }

        $prima = (float) ($inmueble->prima_altura ?? 0);

        // Política SOLO sobre base (no sobre prima)
        $valorPolitica = ($precioBase * $factor) - $precioBase;

        // Final = base + prima + política
        $valorFinal = $precioBase + $prima + $valorPolitica;

        $inmueble->update([
            'valor_politica' => round($valorPolitica),
            'valor_final'    => round($valorFinal),
            // IMPORTANTE: si tu listado usa otro campo, también debes actualizarlo (ver punto 2)
            // 'valor_comercial' => round($valorFinal),
        ]);
    }
    /* ============================================================
     * 5. Llamado principal desde Ventas
     * ============================================================ */
    public function recalcularProyectoPorVenta(Venta $venta): void
    {
        $proyecto = Proyecto::find($venta->id_proyecto);
        if (!$proyecto) return;

        $this->recalcularProyecto($proyecto);
    }
}
