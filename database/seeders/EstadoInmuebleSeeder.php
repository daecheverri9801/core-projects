<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadoInmuebleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('estados_inmueble')->insert([
            ['nombre' => 'Disponible', 'descripcion' => 'Inmueble disponible para venta', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Reservado', 'descripcion' => 'Inmueble con reserva activa', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Vendido', 'descripcion' => 'Inmueble vendido', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'No Disponible', 'descripcion' => 'Inmueble no disponible temporalmente', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}