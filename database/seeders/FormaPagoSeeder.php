<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormaPagoSeeder extends Seeder
{
    public function run()
    {
        DB::table('formas_pago')->insert([
            [
                'forma_pago' => 'Contado',
                'descripcion' => 'Pago total al momento de la compra',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'forma_pago' => 'Financiación Directa',
                'descripcion' => 'Financiación directa con la constructora',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'forma_pago' => 'Crédito Bancario',
                'descripcion' => 'Financiación a través de entidad bancaria',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'forma_pago' => 'Leasing Habitacional',
                'descripcion' => 'Arrendamiento con opción de compra',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'forma_pago' => 'Subsidio + Crédito',
                'descripcion' => 'Combinación de subsidio estatal y crédito',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
