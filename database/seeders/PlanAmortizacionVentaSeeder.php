<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanAmortizacionVentaSeeder extends Seeder
{
    public function run()
    {
        DB::table('planes_amortizacion_venta')->insert([
            [
                'id_venta' => 9,
                'tipo_plan' => 'Francés',
                'valor_interes_anual' => 12.50,
                'plazo_meses' => 60,
                'fecha_inicio' => '2024-02-15',
                'observacion' => 'Plan de amortización a 5 años con tasa fija',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_venta' => 11,
                'tipo_plan' => 'Alemán',
                'valor_interes_anual' => 11.00,
                'plazo_meses' => 120,
                'fecha_inicio' => '2024-04-10',
                'observacion' => 'Plan de amortización a 10 años con cuota decreciente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_venta' => 12,
                'tipo_plan' => 'Francés',
                'valor_interes_anual' => 10.50,
                'plazo_meses' => 24,
                'fecha_inicio' => '2024-05-05',
                'observacion' => 'Plan de amortización a 2 años',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
