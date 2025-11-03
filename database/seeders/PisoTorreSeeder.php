<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PisoTorreSeeder extends Seeder
{
    public function run(): void
    {
        $pisos = [];
        
        // Torre A, B, C - 15 pisos cada una
        for ($torre = 1; $torre <= 3; $torre++) {
            for ($nivel = 1; $nivel <= 15; $nivel++) {
                $pisos[] = [
                    'nivel' => $nivel,
                    'id_torre' => $torre,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }
        
        // Torre Única - 10 pisos
        for ($nivel = 1; $nivel <= 10; $nivel++) {
            $pisos[] = [
                'nivel' => $nivel,
                'id_torre' => 4,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        
        // Torre Norte y Sur - 20 pisos cada una
        for ($torre = 5; $torre <= 6; $torre++) {
            for ($nivel = 1; $nivel <= 20; $nivel++) {
                $pisos[] = [
                    'nivel' => $nivel,
                    'id_torre' => $torre,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }
        
        DB::table('pisos_torre')->insert($pisos);
    }
}