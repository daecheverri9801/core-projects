<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProyectoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('proyectos')->insert([
            [
                'nombre' => 'Torres del Cable',
                'descripcion' => 'Proyecto residencial de lujo en el centro de Manizales',
                'fecha_inicio' => '2023-01-15',
                'fecha_finalizacion' => '2025-12-20',
                'id_ubicacion' => 1,
                'id_estado' => 2,
                'presupuesto_inicial' => 15000000000.00,
                'presupuesto_final' => 16500000000.00,
                'metros_construidos' => 12500.00,
                'cantidad_apartamentos' => 60,
                'cantidad_locales' => 8,
                'cantidad_parqueaderos_vehiculo' => 90,
                'cantidad_parqueaderos_moto' => 30,
                'estrato' => 5,
                'numero_pisos' => 15,
                'numero_torres' => 3,
                'porcentaje_cuota_inicial_min' => 20.00,
                'valor_min_separacion' => 50000000.00,
                'plazo_cuota_inicial_meses' => 12,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'Reserva de Santander',
                'descripcion' => 'Apartamentos modernos con zonas verdes',
                'fecha_inicio' => '2023-02-01',
                'fecha_finalizacion' => '2024-11-30',
                'id_ubicacion' => 2,
                'id_estado' => 3,
                'presupuesto_inicial' => 8500000000.00,
                'presupuesto_final' => 9200000000.00,
                'metros_construidos' => 8000.00,
                'cantidad_apartamentos' => 40,
                'cantidad_locales' => 4,
                'cantidad_parqueaderos_vehiculo' => 60,
                'cantidad_parqueaderos_moto' => 20,
                'estrato' => 4,
                'numero_pisos' => 10,
                'numero_torres' => 1,
                'porcentaje_cuota_inicial_min' => 15.00,
                'valor_min_separacion' => 30000000.00,
                'plazo_cuota_inicial_meses' => 12,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'Poblado Premium',
                'descripcion' => 'Exclusivo proyecto en El Poblado',
                'fecha_inicio' => '2024-03-10',
                'fecha_finalizacion' => '2026-06-15',
                'id_ubicacion' => 3,
                'id_estado' => 2,
                'presupuesto_inicial' => 25000000000.00,
                'presupuesto_final' => 27000000000.00,
                'metros_construidos' => 18000.00,
                'cantidad_apartamentos' => 100,
                'cantidad_locales' => 12,
                'cantidad_parqueaderos_vehiculo' => 150,
                'cantidad_parqueaderos_moto' => 50,
                'estrato' => 6,
                'numero_pisos' => 20,
                'numero_torres' => 2,
                'porcentaje_cuota_inicial_min' => 30.00,
                'valor_min_separacion' => 50000000.00,
                'plazo_cuota_inicial_meses' => 12,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}