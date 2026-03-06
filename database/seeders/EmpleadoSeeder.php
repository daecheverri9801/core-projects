<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmpleadoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('empleados')->insert([
            [
                'nombre' => 'Daniel',
                'apellido' => 'Arango Echeverri',
                'email' => 'daecheverri98@gmail.com',
                'password' => bcrypt('Temporal123!'),
                'telefono' => '3113690744',
                'id_cargo' => 2, 
                'id_dependencia' => 2,
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
