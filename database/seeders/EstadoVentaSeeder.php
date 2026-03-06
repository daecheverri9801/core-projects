<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EstadoVenta;

class EstadoVentaSeeder extends Seeder
{
    public function run()
    {
        $estados = [
            ['estado_venta' => 'En Proceso', 'descripcion' => 'Venta en curso, con documentos en trámite.'],
            ['estado_venta' => 'Aprobada', 'descripcion' => 'Venta aprobada por las partes.'],
            ['estado_venta' => 'Rechazada', 'descripcion' => 'Venta no concretada por alguna razón.'],
            ['estado_venta' => 'Completada', 'descripcion' => 'Venta finalizada con éxito.'],
            ['estado_venta' => 'Cancelada', 'descripcion' => 'Venta cancelada antes de finalizar.'],
            ['estado_venta' => 'En Espera', 'descripcion' => 'Pendiente de algún requisito.'],
        ];

        foreach ($estados as $estado) {
            EstadoVenta::firstOrCreate($estado);
        }
    }
}