<?php

namespace App\Services;

use App\Models\Cargo;
use App\Models\PoliticaComision;
use App\Models\Proyecto;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PoliticaComisionService
{
    public function guardarPoliticasProyecto(int $idProyecto, array $politicas): void
    {
        $proyecto = Proyecto::findOrFail($idProyecto);

        DB::transaction(function () use ($proyecto, $politicas) {
            PoliticaComision::where('id_proyecto', $proyecto->id_proyecto)->delete();

            foreach ($politicas as $item) {
                $idCargo = (int) ($item['id_cargo'] ?? 0);
                $tipoComision = $item['tipo_comision'] ?? null;
                $porcentaje = $item['porcentaje'] ?? null;

                if (!$idCargo || !$tipoComision) {
                    throw new InvalidArgumentException('Cada política debe incluir id_cargo y tipo_comision.');
                }

                if (!in_array($tipoComision, [
                    PoliticaComision::TIPO_VENTA_PROPIA,
                    PoliticaComision::TIPO_VENTA_EQUIPO,
                ], true)) {
                    throw new InvalidArgumentException('El tipo_comision no es válido.');
                }

                Cargo::findOrFail($idCargo);

                PoliticaComision::create([
                    'id_proyecto'    => $proyecto->id_proyecto,
                    'id_cargo'       => $idCargo,
                    'tipo_comision'  => $tipoComision,
                    'porcentaje'     => $porcentaje,
                    'vigente_desde'  => $proyecto->fecha_inicio,
                    'vigente_hasta'  => $proyecto->fecha_finalizacion,
                ]);
            }
        });
    }
}
