<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClienteSeeder extends Seeder
{
    public function run()
    {
        DB::table('clientes')->insert([
            [
                'nombre' => 'Juan Carlos Pérez García',
                'id_tipo_cliente' => 1, // Persona Natural
                'id_tipo_documento' => 1, // Cédula de Ciudadanía
                'documento' => '1053789456',
                'direccion' => 'Calle 23 #45-67, Manizales',
                'telefono' => '3201234567',
                'correo' => 'juan.perez@email.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'María Fernanda López Ruiz',
                'id_tipo_cliente' => 1, // Persona Natural
                'id_tipo_documento' => 1, // Cédula de Ciudadanía
                'documento' => '1053456789',
                'direccion' => 'Carrera 15 #30-20, Manizales',
                'telefono' => '3109876543',
                'correo' => 'maria.lopez@email.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Constructora del Café S.A.S',
                'id_tipo_cliente' => 2, // Persona Jurídica
                'id_tipo_documento' => 3, // NIT
                'documento' => '900123456',
                'direccion' => 'Avenida Santander #50-30, Manizales',
                'telefono' => '6068801234',
                'correo' => 'info@constructoracafe.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Carlos Alberto Gómez Martínez',
                'id_tipo_cliente' => 3, // Inversionista
                'id_tipo_documento' => 1, // Cédula de Ciudadanía
                'documento' => '1053654321',
                'direccion' => 'Calle 50 #25-10, Manizales',
                'telefono' => '3157894561',
                'correo' => 'carlos.gomez@email.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Ana Patricia Rodríguez Silva',
                'id_tipo_cliente' => 1, // Persona Natural
                'id_tipo_documento' => 1, // Cédula de Ciudadanía
                'documento' => '1053987654',
                'direccion' => 'Carrera 20 #40-15, Manizales',
                'telefono' => '3186547890',
                'correo' => 'ana.rodriguez@email.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Inversiones Caldas Ltda',
                'id_tipo_cliente' => 4, // Corporativo
                'id_tipo_documento' => 3, // NIT
                'documento' => '900987654',
                'direccion' => 'Calle 65 #23-45, Manizales',
                'telefono' => '6068805678',
                'correo' => 'contacto@inversionescaldas.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
