<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParqueaderoSeeder extends Seeder
{
    public function run(): void
    {
        $parqueaderos = [];
        
        // Parqueaderos proyecto 1 - 90 parqueaderos
        for ($i = 1; $i <= 90; $i++) {
            $parqueaderos[] = [
                'numero' => 'P-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'tipo' => $i <= 60 ? 'vehiculo' : 'moto',
                'id_apartamento' => $i <= 30 ? $i : null,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        
        // Parqueaderos proyecto 2 - 60 parqueaderos
        for ($i = 1; $i <= 60; $i++) {
            $parqueaderos[] = [
                'numero' => 'P-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'tipo' => $i <= 40 ? 'vehiculo' : 'moto',
                'id_apartamento' => null,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        
        DB::table('parqueaderos')->insert($parqueaderos);
    }
}