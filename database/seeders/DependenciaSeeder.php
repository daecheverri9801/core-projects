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
            ['nombre' => 'Ventas', 'descripcion' => 'Departamento de ventas', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Construcción', 'descripcion' => 'Departamento de construcción', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Contabilidad', 'descripcion' => 'Departamento contable', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Recursos Humanos', 'descripcion' => 'Gestión de personal', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}