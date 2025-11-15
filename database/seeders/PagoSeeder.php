<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PagoSeeder extends Seeder
{
    public function run()
    {
        DB::table('pagos')->insert([
            // Pagos de la Venta 1
            [
                'fecha' => '2024-01-15',
                'id_venta' => 9,
                'referencia_pago' => 'PAG-2024-001',
                'id_concepto_pago' => 1, // Cuota Inicial
                'id_medio_pago' => 2, // Transferencia Bancaria
                'descripcion' => 'Pago cuota inicial apartamento 301',
                'valor' => 30000000.00,
                'id_cuota' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fecha' => '2024-02-15',
                'id_venta' => 9,
                'referencia_pago' => 'PAG-2024-002',
                'id_concepto_pago' => 2, // Cuota Mensual
                'id_medio_pago' => 2, // Transferencia Bancaria
                'descripcion' => 'Pago cuota 1 de 60',
                'valor' => 2680000.00,
                'id_cuota' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fecha' => '2024-03-15',
                'id_venta' => 9,
                'referencia_pago' => 'PAG-2024-003',
                'id_concepto_pago' => 2, // Cuota Mensual
                'id_medio_pago' => 6, // Consignación
                'descripcion' => 'Pago cuota 2 de 60',
                'valor' => 2680000.00,
                'id_cuota' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fecha' => '2024-04-15',
                'id_venta' => 9,
                'referencia_pago' => 'PAG-2024-004',
                'id_concepto_pago' => 2, // Cuota Mensual
                'id_medio_pago' => 2, // Transferencia Bancaria
                'descripcion' => 'Pago cuota 3 de 60',
                'valor' => 2680000.00,
                'id_cuota' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Pagos de la Venta 2 (Contado)
            [
                'fecha' => '2024-02-20',
                'id_venta' => 10,
                'referencia_pago' => 'PAG-2024-005',
                'id_concepto_pago' => 1, // Cuota Inicial (Pago total)
                'id_medio_pago' => 3, // Cheque
                'descripcion' => 'Pago total apartamento 402 - Contado',
                'valor' => 180000000.00,
                'id_cuota' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Pagos de la Venta 3
            [
                'fecha' => '2024-03-10',
                'id_venta' => 11,
                'referencia_pago' => 'PAG-2024-006',
                'id_concepto_pago' => 1, // Cuota Inicial
                'id_medio_pago' => 2, // Transferencia Bancaria
                'descripcion' => 'Pago cuota inicial local comercial 101',
                'valor' => 50000000.00,
                'id_cuota' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Pagos de la Venta 4
            [
                'fecha' => '2024-04-05',
                'id_venta' => 12,
                'referencia_pago' => 'PAG-2024-007',
                'id_concepto_pago' => 1, // Cuota Inicial
                'id_medio_pago' => 2, // Transferencia Bancaria
                'descripcion' => 'Pago cuota inicial apartamento 201',
                'valor' => 40000000.00,
                'id_cuota' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fecha' => '2024-05-05',
                'id_venta' => 12,
                'referencia_pago' => 'PAG-2024-008',
                'id_concepto_pago' => 2, // Cuota Mensual
                'id_medio_pago' => 2, // Transferencia Bancaria
                'descripcion' => 'Pago cuota 1 de 24',
                'valor' => 4400000.00,
                'id_cuota' => 13, // Primera cuota del plan 3
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fecha' => '2024-06-05',
                'id_venta' => 12,
                'referencia_pago' => 'PAG-2024-009',
                'id_concepto_pago' => 2, // Cuota Mensual
                'id_medio_pago' => 2, // Transferencia Bancaria
                'descripcion' => 'Pago cuota 2 de 24',
                'valor' => 4400000.00,
                'id_cuota' => 14, // Segunda cuota del plan 3
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
