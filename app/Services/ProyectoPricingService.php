<?php

namespace App\Services;

use App\Models\Proyecto;
use App\Models\Venta;
use App\Models\Apartamento;
use App\Models\EstadoInmueble;
use Illuminate\Support\Facades\DB;

class ProyectoPricingService
{
    public function recalcPreciosProyecto(int $idProyecto): void
    {
        DB::transaction(function () use ($idProyecto) {

            /** @var Proyecto $proyecto */
            $proyecto = Proyecto::with('politicasPrecio')
                ->lockForUpdate()
                ->findOrFail($idProyecto);

            /* ============================================================
         * 1. Obtener ventas activas (venta + separación)
         * ============================================================ */
            $ventasActivas = Venta::where('id_proyecto', $idProyecto)
                ->whereIn('tipo_operacion', ['venta', 'separacion'])
                ->count();

            /* ============================================================
         * 2. Calcular cuántos bloques *deberían* estar activos
         * ============================================================ */
            $politicas = $proyecto->politicasPrecio()
                ->orderBy('id_politica_precio')
                ->get();

            $ventasRestantes = $ventasActivas;
            $bloquesShould = 0;

            foreach ($politicas as $p) {
                if ($ventasRestantes >= $p->ventas_por_escalon) {
                    $bloquesShould++;
                    $ventasRestantes -= $p->ventas_por_escalon;
                } else {
                    break;
                }
            }
            $newBlocks = $bloquesShould - $proyecto->bloques_aplicados;

            if ($newBlocks <= 0) {
                return;
            }

            /* ============================================================
         * 3. Obtener SOLO los bloques nuevos
         * ============================================================ */
            $bloquesNuevos = $politicas->slice($proyecto->bloques_aplicados, $newBlocks);


            /* ============================================================
         * 4. Aplicar SOLO los bloques nuevos
         * ============================================================ */
            $estadoDisponibleId = EstadoInmueble::where('nombre', 'Disponible')
                ->value('id_estado_inmueble');

            $apartamentos = Apartamento::where('id_estado_inmueble', $estadoDisponibleId)
                ->whereHas('torre', fn($q) => $q->where('id_proyecto', $idProyecto))
                ->lockForUpdate()
                ->get();

            foreach ($bloquesNuevos as $bloque) {

                $incremento = $bloque->porcentaje_aumento / 100;


                foreach ($apartamentos as $apto) {

                    $old = $apto->valor_final;
                    $new = round($old * (1 + $incremento));

                    $apto->update([
                        'valor_final' => $new,
                    ]);
                }

                // marcar bloque como aplicado
                $proyecto->increment('bloques_aplicados');
            }

        });
    }
}
