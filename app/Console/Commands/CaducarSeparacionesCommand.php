<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Venta;
use App\Models\EstadoInmueble;
use Carbon\Carbon;

class CaducarSeparacionesCommand extends Command
{
    protected $signature = 'ventas:caducar-separaciones';
    protected $description = 'Caduca separaciones vencidas y libera el inmueble';

    public function handle()
    {
        $hoy = Carbon::today();

        $idEstadoSeparado = EstadoInmueble::where('nombre', 'Separado')->value('id_estado_inmueble');
        $idEstadoDisponible = EstadoInmueble::where('nombre', 'Disponible')->value('id_estado_inmueble');

        if (!$idEstadoSeparado || !$idEstadoDisponible) {
            $this->error('No se encontraron estados Separado/Disponible en estados_inmueble.');
            return Command::FAILURE;
        }

        $separaciones = Venta::where('tipo_operacion', 'separacion')
            ->where('estado_operacion', 'vigente')
            ->whereDate('fecha_limite_separacion', '<', $hoy)
            ->get();

        $totalCaducadas = 0;

        foreach ($separaciones as $sep) {

            $inmueble = $sep->apartamento ?? $sep->local ?? null;
            if (!$inmueble) {
                continue;
            }

            // Solo caducamos si el inmueble sigue separado
            if ($inmueble->id_estado_inmueble != $idEstadoSeparado) {
                continue;
            }

            // Verificar si ya existe una venta posterior para el mismo inmueble
            $tieneVentaPosterior = Venta::where('tipo_operacion', 'venta')
                ->where('id_proyecto', $sep->id_proyecto)
                ->when($sep->id_apartamento, fn($q) => $q->where('id_apartamento', $sep->id_apartamento))
                ->when($sep->id_local, fn($q) => $q->where('id_local', $sep->id_local))
                ->whereDate('fecha_venta', '>=', $sep->fecha_limite_separacion)
                ->exists();

            if ($tieneVentaPosterior) {
                // Ya hubo venta → no caducar
                continue;
            }

            // Marcar separación como caducada
            $sep->update([
                'estado_operacion' => 'caducada',
            ]);

            // Devolver inmueble a Disponible
            $inmueble->update([
                'id_estado_inmueble' => $idEstadoDisponible,
            ]);

            $totalCaducadas++;
        }

        $this->info("Separaciones caducadas: {$totalCaducadas}");

        return Command::SUCCESS;
    }
}
