<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('estados')->insert([
            ['nombre' => 'Planeación', 'descripcion' => 'Proyecto en fase de planeación', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'En Construcción', 'descripcion' => 'Proyecto en construcción activa', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'En Venta', 'descripcion' => 'Proyecto disponible para venta', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Finalizado', 'descripcion' => 'Proyecto completado', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Suspendido', 'descripcion' => 'Proyecto temporalmente suspendido', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}