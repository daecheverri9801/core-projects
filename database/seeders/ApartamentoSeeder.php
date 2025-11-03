<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApartamentoSeeder extends Seeder
{
    public function run(): void
    {
        $apartamentos = [];
        $estados = [1, 1, 1, 2, 3]; // Mayoría disponibles
        $tipos = [2, 3, 3, 4]; // Mayoría 2 y 3 habitaciones
        
        // Torre A - 4 apartamentos por piso (pisos 1-15)
        for ($piso = 1; $piso <= 15; $piso++) {
            for ($apto = 1; $apto <= 4; $apto++) {
                $numero = $piso . str_pad($apto, 2, '0', STR_PAD_LEFT);
                $tipoApto = $tipos[array_rand($tipos)];
                $valorM2 = [5500000, 6000000, 6000000, 6500000][$tipoApto - 2];
                $area = [55, 75, 75, 95][$tipoApto - 2];
                $valorTotal = $valorM2 * $area;
                
                $apartamentos[] = [
                    'numero' => $numero,
                    'valor_total' => $valorTotal,
                    'id_piso_torre' => $piso,
                    'id_tipo_apartamento' => $tipoApto,
                    'id_estado_inmueble' => $estados[array_rand($estados)],
                    'id_torre' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }
        
        DB::table('apartamentos')->insert($apartamentos);
    }
}