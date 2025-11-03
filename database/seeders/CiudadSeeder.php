<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CiudadSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ciudades')->insert([
            ['nombre' => 'Manizales', 'codigo_postal' => '170001', 'id_departamento' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Chinchiná', 'codigo_postal' => '170002', 'id_departamento' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Villamaría', 'codigo_postal' => '170003', 'id_departamento' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Medellín', 'codigo_postal' => '050001', 'id_departamento' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Envigado', 'codigo_postal' => '055040', 'id_departamento' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Cali', 'codigo_postal' => '760001', 'id_departamento' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Palmira', 'codigo_postal' => '763001', 'id_departamento' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Bogotá', 'codigo_postal' => '110111', 'id_departamento' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Barranquilla', 'codigo_postal' => '080001', 'id_departamento' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}