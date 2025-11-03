<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartamentoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('departamentos')->insert([
            ['nombre' => 'Caldas', 'id_pais' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Antioquia', 'id_pais' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Valle del Cauca', 'id_pais' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Cundinamarca', 'id_pais' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Risaralda', 'id_pais' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}