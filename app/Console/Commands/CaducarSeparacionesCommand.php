<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Venta;
use App\Models\EstadoInmueble;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CaducarSeparacionesCommand extends Command
{
    protected $signature = 'ventas:caducar-separaciones';
    protected $description = 'Caduca separaciones vencidas y libera el inmueble';

    public function handle()
    {
        $hoy = Carbon::today();

        $idSeparado = EstadoInmueble::where('nombre', 'Separado')->value('id_estado_inmueble');
        $idDisponible = EstadoInmueble::where('nombre', 'Disponible')->value('id_estado_inmueble');

        if (!$idSeparado || !$idDisponible) {
            $this->error('No se encontraron estados Separado/Disponible en estados_inmueble.');
            return Command::FAILURE;
        }

        $totalCaducadas = 0;

        DB::transaction(function () use ($hoy, $idSeparado, $idDisponible, &$totalCaducadas) {

            $separaciones = Venta::where('tipo_operacion', 'separacion')
                ->where('estado_operacion', 'vigente')
                ->whereNotNull('fecha_limite_separacion')
                ->whereDate('fecha_limite_separacion', '<', $hoy)
                ->lockForUpdate()
                ->get();

            foreach ($separaciones as $sep) {

                $inmueble = $sep->apartamento ?? $sep->local;
                if (!$inmueble) continue;

                // Si ya no está separado, no tocar
                if ((int)$inmueble->id_estado_inmueble !== (int)$idSeparado) {
                    continue;
                }

                // Si ya existe una venta para el mismo inmueble, no caducar
                $existeVenta = Venta::where('tipo_operacion', 'venta')
                    ->when($sep->id_apartamento, fn($q) => $q->where('id_apartamento', $sep->id_apartamento))
                    ->when($sep->id_local, fn($q) => $q->where('id_local', $sep->id_local))
                    ->exists();

                if ($existeVenta) continue;

                // Caducar separación
                $sep->update([
                    'estado_operacion' => 'caducada',
                ]);

                // Liberar inmueble
                $inmueble->update([
                    'id_estado_inmueble' => $idDisponible,
                ]);

                app(\App\Services\PriceEngine::class)
                    ->recalcularProyecto($sep->proyecto ?? \App\Models\Proyecto::find($sep->id_proyecto));

                $totalCaducadas++;
            }
        });

        $this->info("Separaciones caducadas: {$totalCaducadas}");
        return Command::SUCCESS;
    }
}
