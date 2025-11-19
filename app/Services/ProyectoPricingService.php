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

            \Log::info("=== Recalculo Proyecto {$proyecto->id_proyecto} ===");

            /* ============================================================
         * 1. Obtener ventas activas (venta + separación)
         * ============================================================ */
            $ventasActivas = Venta::where('id_proyecto', $idProyecto)
                ->whereIn('tipo_operacion', ['venta', 'separacion'])
                ->count();

            \Log::info("Ventas activas = $ventasActivas");


            /* ============================================================
         * 2. Calcular cuántos bloques *deberían* estar activos
         * ============================================================ */
            $politicas = $proyecto->politicasPrecio()
                ->orderBy('id_politica')
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

            \Log::info("Bloques que deberían estar activos = $bloquesShould");
            \Log::info("Bloques ya aplicados = {$proyecto->bloques_aplicados}");

            $newBlocks = $bloquesShould - $proyecto->bloques_aplicados;

            \Log::info("Nuevos bloques por aplicar = $newBlocks");

            if ($newBlocks <= 0) {
                \Log::info("No hay bloques nuevos. Finaliza proceso.");
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

                \Log::info("Aplicando bloque ID {$bloque->id_politica} incremento = {$bloque->porcentaje_aumento}%");

                foreach ($apartamentos as $apto) {

                    $old = $apto->valor_final;
                    $new = round($old * (1 + $incremento));

                    \Log::info("Apto {$apto->id_apartamento}: $old → $new");

                    $apto->update([
                        'valor_final' => $new,
                    ]);
                }

                // marcar bloque como aplicado
                $proyecto->increment('bloques_aplicados');
            }

            \Log::info("Total bloques aplicados ahora = {$proyecto->bloques_aplicados}");
        });
    }
}
