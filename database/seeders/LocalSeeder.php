<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocalSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('locales')->insert([
            [
                'numero' => 'L-101',
                'id_estado_inmueble' => 1,
                'area_total_local' => 45.50,
                'id_torre' => 1,
                'id_piso_torre' => 1,
                'valor_m2' => 5500000.00,
                'valor_total' => 249250000.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'numero' => 'L-102',
                'id_estado_inmueble' => 1,
                'area_total_local' => 52.30,
                'id_torre' => 1,
                'id_piso_torre' => 1,
                'valor_m2' => 5500000.00,
                'valor_total' => 287650000.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'numero' => 'L-103',
                'id_estado_inmueble' => 2,
                'area_total_local' => 38.75,
                'id_torre' => 1,
                'id_piso_torre' => 1,
                'valor_m2' => 5500000.00,
                'valor_total' => 213125000.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'numero' => 'L-104',
                'id_estado_inmueble' => 1,
                'area_total_local' => 60.00,
                'id_torre' => 1,
                'id_piso_torre' => 1,
                'valor_m2' => 5500000.00,
                'valor_total' => 330000000.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'numero' => 'L-105',
                'id_estado_inmueble' => 3,
                'area_total_local' => 48.20,
                'id_torre' => 1,
                'id_piso_torre' => 1,
                'valor_m2' => 5500000.00,
                'valor_total' => 265100000.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}