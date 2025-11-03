<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoApartamentoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tipos_apartamento')->insert([
            [
                'nombre' => 'Tipo 1',
                'area_construida' => 45.00,
                'area_privada' => 38.00,
                'cantidad_habitaciones' => 0,
                'cantidad_banos' => 1,
                'valor_m2' => 5000000.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'Tipo 2',
                'area_construida' => 55.00,
                'area_privada' => 48.00,
                'cantidad_habitaciones' => 1,
                'cantidad_banos' => 1,
                'valor_m2' => 5500000.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'Tipo 3',
                'area_construida' => 75.00,
                'area_privada' => 65.00,
                'cantidad_habitaciones' => 2,
                'cantidad_banos' => 2,
                'valor_m2' => 6000000.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'Tipo 4',
                'area_construida' => 95.00,
                'area_privada' => 82.00,
                'cantidad_habitaciones' => 3,
                'cantidad_banos' => 2,
                'valor_m2' => 6500000.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'Penthouse',
                'area_construida' => 150.00,
                'area_privada' => 130.00,
                'cantidad_habitaciones' => 4,
                'cantidad_banos' => 3,
                'valor_m2' => 8000000.00,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}