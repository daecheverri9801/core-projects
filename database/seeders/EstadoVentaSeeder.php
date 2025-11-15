<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadoVentaSeeder extends Seeder
{
    public function run()
    {
        DB::table('estados_venta')->insert([
            [
                'estado_venta' => 'Promesa',
                'descripcion' => 'Promesa de compraventa firmada',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'estado_venta' => 'Separado',
                'descripcion' => 'Inmueble separado con cuota inicial',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'estado_venta' => 'En Financiación',
                'descripcion' => 'En proceso de aprobación de crédito',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'estado_venta' => 'Escriturado',
                'descripcion' => 'Escritura pública firmada',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'estado_venta' => 'Entregado',
                'descripcion' => 'Inmueble entregado al cliente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'estado_venta' => 'Cancelado',
                'descripcion' => 'Venta cancelada o anulada',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
