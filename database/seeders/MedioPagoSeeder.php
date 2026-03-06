<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MedioPago;

class MedioPagoSeeder extends Seeder
{
    public function run()
    {
        $medios = [
            ['medio_pago' => 'Efectivo', 'descripcion' => 'Pago en moneda corriente.'],
            ['medio_pago' => 'Cheque', 'descripcion' => 'Cheque certificado o de gerencia.'],
            ['medio_pago' => 'Transferencia Bancaria', 'descripcion' => 'Transferencia electrónica de fondos.'],
            ['medio_pago' => 'Tarjeta de Crédito', 'descripcion' => 'Pago con tarjeta de crédito.'],
            ['medio_pago' => 'Tarjeta Débito', 'descripcion' => 'Pago con tarjeta de débito.'],
            ['medio_pago' => 'Giro', 'descripcion' => 'Giro postal o bancario.'],
            ['medio_pago' => 'Depósito en Cuenta', 'descripcion' => 'Consignación en cuenta bancaria.'],
        ];

        foreach ($medios as $medio) {
            MedioPago::firstOrCreate($medio);
        }
    }
}