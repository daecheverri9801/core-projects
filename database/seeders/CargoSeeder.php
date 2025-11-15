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
            ['nombre' => 'Administrador', 'descripcion' => 'Responsable de soporte TI', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}