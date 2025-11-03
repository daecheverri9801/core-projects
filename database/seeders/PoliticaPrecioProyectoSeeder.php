<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PoliticaPrecioProyectoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('politicas_precio_proyecto')->insert([
            [
                'id_proyecto' => 1,
                'ventas_por_escalon' => 10,
                'porcentaje_aumento' => 2.50,
                'aplica_desde' => '2024-01-15',
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id_proyecto' => 2,
                'ventas_por_escalon' => 5,
                'porcentaje_aumento' => 3.00,
                'aplica_desde' => '2023-06-01',
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id_proyecto' => 3,
                'ventas_por_escalon' => 15,
                'porcentaje_aumento' => 2.00,
                'aplica_desde' => '2024-03-01',
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}