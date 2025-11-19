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
    public function obtenerBloqueActual(Proyecto $proyecto)
    {
        // Ventas activas: venta o separación que NO estén eliminadas
        $ventas = Venta::where('id_proyecto', $proyecto->id_proyecto)->count();

        $politicas = $proyecto->politicasPrecio()->get();

        $bloque = 0;
        $acumulado = 0;

        foreach ($politicas as $p) {
            $acumulado += $p->ventas_por_escalon;

            if ($ventas >= $acumulado) {
                $bloque++;
            } else {
                break;
            }
        }

        return $bloque; // Ej: bloque 0, 1, 2, 3...
    }

    /* ============================================================
     * 2. Calcular el multiplicador de precios acumulado
     * ============================================================ */
    public function calcularFactorAumento(Proyecto $proyecto, $bloqueActual)
    {
        $politicas = $proyecto->politicasPrecio()->get();

        $factor = 1;

        for ($i = 0; $i < $bloqueActual; $i++) {
            if (!isset($politicas[$i])) break;

            $aumento = $politicas[$i]->porcentaje_aumento;
            $factor *= (1 + ($aumento / 100));
        }

        return $factor;
    }

    /* ============================================================
     * 3. Recalcular todos los inmuebles disponibles del proyecto
     * ============================================================ */
    public function recalcularProyecto(Proyecto $proyecto)
    {
        $bloque = $this->obtenerBloqueActual($proyecto);
        $factor = $this->calcularFactorAumento($proyecto, $bloque);

        // Recalcular apartamentos disponibles
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

        // Recalcular locales disponibles
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
            $tipo = $inmueble->tipoApartamento;
            $precioBase = $tipo->valor_estimado ?? 0;  // ✔ correcto
        } else {
            $precioBase = ($inmueble->valor_m2 ?? 0) * ($inmueble->area_total_local ?? 0);
        }


        $prima = $inmueble->prima_altura ?? 0;
        $valorPolitica = ($precioBase * $factor) - $precioBase;

        $valorFinal = $precioBase + $prima + $valorPolitica;

        $inmueble->update([
            'valor_politica' => round($valorPolitica),
            'valor_final'    => round($valorFinal),
        ]);
    }

    /* ============================================================
     * 5. Llamado principal desde Ventas
     * ============================================================ */
    public function recalcularProyectoPorVenta(Venta $venta)
    {
        $proyecto = Proyecto::find($venta->id_proyecto);
        if (!$proyecto) return;

        $this->recalcularProyecto($proyecto);
    }
}
