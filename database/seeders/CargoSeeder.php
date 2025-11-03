<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CargoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cargos')->insert([
            ['nombre' => 'Gerente General', 'descripcion' => 'Responsable de la gestión general', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Gerente de Ventas', 'descripcion' => 'Responsable del área de ventas', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Asesor Comercial', 'descripcion' => 'Asesor de ventas de inmuebles', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Arquitecto', 'descripcion' => 'Responsable del diseño arquitectónico', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Ingeniero Civil', 'descripcion' => 'Responsable de la construcción', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Contador', 'descripcion' => 'Responsable del área contable', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}