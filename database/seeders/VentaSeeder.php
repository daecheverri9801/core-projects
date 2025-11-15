<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VentaSeeder extends Seeder
{
    public function run()
    {
        DB::table('ventas')->insert([
            [
                'id_empleado' => 1, // Asegúrate de tener empleados creados
                'documento_cliente' => '1053789456',
                'fecha_venta' => '2024-01-15',
                'fecha_vencimiento' => '2029-01-15',
                'id_apartamento' => 65, // Asegúrate de tener apartamentos creados
                'id_local' => null,
                'id_proyecto' => 9, // Asegúrate de tener proyectos creados
                'id_forma_pago' => 2, // Financiación Directa
                'id_estado_venta' => 2, // Separado
                'cuota_inicial' => 30000000.00,
                'valor_restante' => 120000000.00,
                'descripcion' => 'Venta apartamento 301 Torre A',
                'valor_base' => 141509434.00,
                'iva' => 8490566.00,
                'valor_total' => 150000000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_empleado' => 1,
                'documento_cliente' => '1053456789',
                'fecha_venta' => '2024-02-20',
                'fecha_vencimiento' => null,
                'id_apartamento' => 64,
                'id_local' => null,
                'id_proyecto' => 9,
                'id_forma_pago' => 1, // Contado
                'id_estado_venta' => 4, // Escriturado
                'cuota_inicial' => 180000000.00,
                'valor_restante' => 0.00,
                'descripcion' => 'Venta apartamento 402 Torre B - Pago de contado',
                'valor_base' => 169811321.00,
                'iva' => 10188679.00,
                'valor_total' => 180000000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_empleado' => 2,
                'documento_cliente' => '900123456',
                'fecha_venta' => '2024-03-10',
                'fecha_vencimiento' => '2034-03-10',
                'id_apartamento' => null,
                'id_local' => 8, // Asegúrate de tener locales creados
                'id_proyecto' => 9,
                'id_forma_pago' => 3, // Crédito Bancario
                'id_estado_venta' => 3, // En Financiación
                'cuota_inicial' => 50000000.00,
                'valor_restante' => 200000000.00,
                'descripcion' => 'Venta local comercial 101',
                'valor_base' => 235849057.00,
                'iva' => 14150943.00,
                'valor_total' => 250000000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_empleado' => 2,
                'documento_cliente' => '1053654321',
                'fecha_venta' => '2024-04-05',
                'fecha_vencimiento' => '2026-04-05',
                'id_apartamento' => 63,
                'id_local' => null,
                'id_proyecto' => 9,
                'id_forma_pago' => 2, // Financiación Directa
                'id_estado_venta' => 2, // Separado
                'cuota_inicial' => 40000000.00,
                'valor_restante' => 95000000.00,
                'descripcion' => 'Venta apartamento 201 Torre C',
                'valor_base' => 127358491.00,
                'iva' => 7641509.00,
                'valor_total' => 135000000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
