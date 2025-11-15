<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DependenciaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('dependencias')->insert([
            ['nombre' => 'Gerencia', 'descripcion' => 'Área de gerencia general', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Administración', 'descripcion' => 'Departamento de administración', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}