<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConceptoPagoSeeder extends Seeder
{
    public function run()
    {
        DB::table('conceptos_pago')->insert([
            [
                'concepto' => 'Cuota Inicial',
                'descripcion' => 'Pago inicial de la venta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'concepto' => 'Cuota Mensual',
                'descripcion' => 'Pago mensual del plan de amortización',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'concepto' => 'Abono a Capital',
                'descripcion' => 'Abono extraordinario al capital',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'concepto' => 'Pago de Intereses',
                'descripcion' => 'Pago de intereses de financiación',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'concepto' => 'Gastos Notariales',
                'descripcion' => 'Pago de escrituración y registro',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'concepto' => 'Administración',
                'descripcion' => 'Pago de administración del inmueble',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'concepto' => 'Saldo Final',
                'descripcion' => 'Pago del saldo restante',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
