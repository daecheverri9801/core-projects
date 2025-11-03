<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TorreSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('torres')->insert([
            ['nombre_torre' => 'Torre A', 'numero_pisos' => 15, 'id_proyecto' => 1, 'id_estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre_torre' => 'Torre B', 'numero_pisos' => 15, 'id_proyecto' => 1, 'id_estado' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['nombre_torre' => 'Torre C', 'numero_pisos' => 12, 'id_proyecto' => 1, 'id_estado' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['nombre_torre' => 'Torre Única', 'numero_pisos' => 10, 'id_proyecto' => 2, 'id_estado' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['nombre_torre' => 'Torre Norte', 'numero_pisos' => 20, 'id_proyecto' => 3, 'id_estado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre_torre' => 'Torre Sur', 'numero_pisos' => 20, 'id_proyecto' => 3, 'id_estado' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}