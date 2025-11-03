<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UbicacionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ubicaciones')->insert([
            ['barrio' => 'Cable', 'direccion' => 'Carrera 23 # 65-45', 'id_ciudad' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['barrio' => 'Versalles', 'direccion' => 'Avenida Santander # 45-30', 'id_ciudad' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['barrio' => 'El Poblado', 'direccion' => 'Calle 50 # 30-20', 'id_ciudad' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['barrio' => 'Laureles', 'direccion' => 'Carrera 43A # 1-50', 'id_ciudad' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['barrio' => 'Chapinero', 'direccion' => 'Calle 72 # 10-34', 'id_ciudad' => 8, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}