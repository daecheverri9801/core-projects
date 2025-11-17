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
            $proyecto = Proyecto::with('politicasPrecio')->findOrFail($idProyecto);

            // 1. Contar operaciones activas (ventas + separaciones)
            $ventasActivas = Venta::where('id_proyecto', $idProyecto)
                ->whereHas('apartamento.estadoInmueble', function ($q) {
                    $q->whereIn('nombre', ['Vendido', 'Separado']);
                })
                ->orWhereHas('local.estadoInmueble', function ($q) {
                    $q->whereIn('nombre', ['Vendido', 'Separado']);
                })
                ->count();

            // 2. Determinar bloques activos
            $politicas = $proyecto->politicasPrecio()->get();

            $restantes = $ventasActivas;
            $bloquesActivos = [];
            foreach ($politicas as $p) {
                if ($restantes >= $p->ventas_por_escalon) {
                    $bloquesActivos[] = $p;
                    $restantes -= $p->ventas_por_escalon;
                } else {
                    break;
                }
            }

            $factor = 1.0;
            foreach ($bloquesActivos as $p) {
                $factor *= (1 + ($p->porcentaje_incremento / 100));
            }

            // 3. Recalcular precios de apartamentos disponibles
            $estadoDisponibleId = EstadoInmueble::where('nombre', 'Disponible')
                ->value('id_estado_inmueble');

            $apartamentos = Apartamento::whereHas('torre', function ($q) use ($idProyecto) {
                $q->where('id_proyecto', $idProyecto);
            })
                ->where('id_estado_inmueble', $estadoDisponibleId)
                ->get();

            foreach ($apartamentos as $apto) {
                $base = (float)($apto->precio_base_vigente ?? 0);
                $prima = (float)($apto->prima_altura ?? 0);

                $valorPolitica = $base * ($factor - 1);
                $valorFinal = $base + $prima + $valorPolitica;

                $apto->update([
                    'valor_politica' => $valorPolitica,
                    'valor_final' => $valorFinal,
                ]);
            }
            

            // Análogo si quieres hacer lo mismo con locales
        });
    }
}
