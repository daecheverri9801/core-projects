<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PoliticaComisionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('politicas_comision')->insert([
            [
                'id_proyecto' => 1,
                'aplica_a' => 'asesor',
                'base_calculo' => 'porcentaje',
                'porcentaje' => 3.50,
                'valor_fijo' => null,
                'minimo_venta_estado' => 5,
                'descripcion' => 'Comisión estándar para asesores comerciales',
                'vigente_desde' => '2024-01-01',
                'vigente_hasta' => '2024-12-31',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id_proyecto' => 1,
                'aplica_a' => 'gerente',
                'base_calculo' => 'porcentaje',
                'porcentaje' => 1.50,
                'valor_fijo' => null,
                'minimo_venta_estado' => 10,
                'descripcion' => 'Comisión para gerente de ventas',
                'vigente_desde' => '2024-01-01',
                'vigente_hasta' => '2024-12-31',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id_proyecto' => 2,
                'aplica_a' => 'asesor',
                'base_calculo' => 'porcentaje',
                'porcentaje' => 4.00,
                'valor_fijo' => null,
                'minimo_venta_estado' => 3,
                'descripcion' => 'Comisión premium proyecto 2',
                'vigente_desde' => '2024-01-01',
                'vigente_hasta' => '2025-12-31',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id_proyecto' => 3,
                'aplica_a' => 'asesor',
                'base_calculo' => 'valor_fijo',
                'porcentaje' => null,
                'valor_fijo' => 15000000.00,
                'minimo_venta_estado' => 1,
                'descripcion' => 'Comisión fija proyecto premium',
                'vigente_desde' => '2024-03-01',
                'vigente_hasta' => '2026-09-30',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}