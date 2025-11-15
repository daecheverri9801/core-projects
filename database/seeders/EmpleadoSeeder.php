<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmpleadoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('empleados')->insert([
            // Gerencia
            [
                'nombre' => 'Carlos',
                'apellido' => 'Rodríguez',
                'email' => 'carlos.rodriguez@coreprojects.com',
                'password' => bcrypt('password123'),
                'telefono' => '3001234567',
                'id_cargo' => 2, 
                'id_dependencia' => 2,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'María',
                'apellido' => 'González',
                'email' => 'maria.gonzalez@coreprojects.com',
                'password' => bcrypt('password123'),
                'telefono' => '3002345678',
                'id_cargo' => 1,
                'id_dependencia' => 1, 
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],

        ]);
    }
}
