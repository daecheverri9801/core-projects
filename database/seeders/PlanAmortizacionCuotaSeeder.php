<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanAmortizacionCuotaSeeder extends Seeder
{
    public function run()
    {
        // Cuotas para el Plan 1 (Venta 1 - 60 meses)
        $cuotasPlan1 = [];
        $saldo = 120000000.00;
        $tasaMensual = 12.50 / 12 / 100;
        $cuotaMensual = $saldo * ($tasaMensual * pow(1 + $tasaMensual, 60)) / (pow(1 + $tasaMensual, 60) - 1);

        for ($i = 1; $i <= 60; $i++) {
            $interes = $saldo * $tasaMensual;
            $capital = $cuotaMensual - $interes;
            $saldo -= $capital;

            $cuotasPlan1[] = [
                'id_plan' => 4,
                'numero_cuota' => $i,
                'fecha_vencimiento' => date('Y-m-d', strtotime('2024-02-15 +' . $i . ' months')),
                'valor_cuota' => round($cuotaMensual, 2),
                'valor_interes' => round($interes, 2),
                'valor_capital' => round($capital, 2),
                'saldo' => round($saldo, 2),
                'estado' => $i <= 3 ? 'Pagada' : 'Pendiente',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insertar solo las primeras 12 cuotas como ejemplo
        DB::table('planes_amortizacion_cuota')->insert(array_slice($cuotasPlan1, 0, 12));

        // Cuotas para el Plan 3 (Venta 4 - 24 meses)
        $cuotasPlan3 = [];
        $saldo = 95000000.00;
        $tasaMensual = 10.50 / 12 / 100;
        $cuotaMensual = $saldo * ($tasaMensual * pow(1 + $tasaMensual, 24)) / (pow(1 + $tasaMensual, 24) - 1);

        for ($i = 1; $i <= 24; $i++) {
            $interes = $saldo * $tasaMensual;
            $capital = $cuotaMensual - $interes;
            $saldo -= $capital;

            $cuotasPlan3[] = [
                'id_plan' => 6,
                'numero_cuota' => $i,
                'fecha_vencimiento' => date('Y-m-d', strtotime('2024-05-05 +' . $i . ' months')),
                'valor_cuota' => round($cuotaMensual, 2),
                'valor_interes' => round($interes, 2),
                'valor_capital' => round($capital, 2),
                'saldo' => round($saldo, 2),
                'estado' => $i <= 2 ? 'Pagada' : 'Pendiente',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insertar solo las primeras 6 cuotas como ejemplo
        DB::table('planes_amortizacion_cuota')->insert(array_slice($cuotasPlan3, 0, 6));
    }
}
