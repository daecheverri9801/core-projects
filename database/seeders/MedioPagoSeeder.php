<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedioPagoSeeder extends Seeder
{
    public function run()
    {
        DB::table('medios_pago')->insert([
            [
                'medio_pago' => 'Efectivo',
                'descripcion' => 'Pago en efectivo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'medio_pago' => 'Transferencia Bancaria',
                'descripcion' => 'Transferencia electrónica entre cuentas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'medio_pago' => 'Cheque',
                'descripcion' => 'Pago con cheque',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'medio_pago' => 'Tarjeta de Crédito',
                'descripcion' => 'Pago con tarjeta de crédito',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'medio_pago' => 'Tarjeta Débito',
                'descripcion' => 'Pago con tarjeta débito',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'medio_pago' => 'Consignación',
                'descripcion' => 'Consignación en cuenta bancaria',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'medio_pago' => 'PSE',
                'descripcion' => 'Pago electrónico PSE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
