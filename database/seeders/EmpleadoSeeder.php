<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmpleadoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('empleados')->insert([
            // Gerencia
            [
                'nombre' => 'Carlos',
                'apellido' => 'Rodríguez',
                'email' => 'carlos.rodriguez@coreprojects.com',
                'password' => bcrypt('password123'),
                'telefono' => '3001234567',
                'id_cargo' => 1, // Gerente General
                'id_dependencia' => 1, // Gerencia
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'María',
                'apellido' => 'González',
                'email' => 'maria.gonzalez@coreprojects.com',
                'password' => bcrypt('password123'),
                'telefono' => '3002345678',
                'id_cargo' => 2, // Subgerente
                'id_dependencia' => 1, // Gerencia
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // Comercial
            [
                'nombre' => 'Juan',
                'apellido' => 'Martínez',
                'email' => 'juan.martinez@coreprojects.com',
                'password' => bcrypt('password123'),
                'telefono' => '3003456789',
                'id_cargo' => 3, // Director Comercial
                'id_dependencia' => 2, // Comercial
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'Ana',
                'apellido' => 'López',
                'email' => 'ana.lopez@coreprojects.com',
                'password' => bcrypt('password123'),
                'telefono' => '3004567890',
                'id_cargo' => 4, // Asesor Comercial
                'id_dependencia' => 2, // Comercial
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'Pedro',
                'apellido' => 'Sánchez',
                'email' => 'pedro.sanchez@coreprojects.com',
                'password' => bcrypt('password123'),
                'telefono' => '3005678901',
                'id_cargo' => 4, // Asesor Comercial
                'id_dependencia' => 2, // Comercial
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'Laura',
                'apellido' => 'Ramírez',
                'email' => 'laura.ramirez@coreprojects.com',
                'password' => bcrypt('password123'),
                'telefono' => '3006789012',
                'id_cargo' => 5, // Coordinador Ventas
                'id_dependencia' => 2, // Comercial
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // Contabilidad
            [
                'nombre' => 'Luis',
                'apellido' => 'Hernández',
                'email' => 'luis.hernandez@coreprojects.com',
                'password' => bcrypt('password123'),
                'telefono' => '3007890123',
                'id_cargo' => 6, // Jefe Contabilidad
                'id_dependencia' => 3, // Contabilidad
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'Carmen',
                'apellido' => 'Torres',
                'email' => 'carmen.torres@coreprojects.com',
                'password' => bcrypt('password123'),
                'telefono' => '3008901234',
                'id_cargo' => 4, // Contador
                'id_dependencia' => 3, // Contabilidad
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'Jorge',
                'apellido' => 'Flores',
                'email' => 'jorge.flores@coreprojects.com',
                'password' => bcrypt('password123'),
                'telefono' => '3009012345',
                'id_cargo' => 2, // Auxiliar Contable
                'id_dependencia' => 3, // Contabilidad
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // Obra
            [
                'nombre' => 'Miguel',
                'apellido' => 'Vargas',
                'email' => 'miguel.vargas@coreprojects.com',
                'password' => bcrypt('password123'),
                'telefono' => '3010123456',
                'id_cargo' => 3, // Jefe de Obra
                'id_dependencia' => 4, // Obra
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'Sofía',
                'apellido' => 'Castro',
                'email' => 'sofia.castro@coreprojects.com',
                'password' => bcrypt('password123'),
                'telefono' => '3011234567',
                'id_cargo' => 5, // Supervisor de Obra
                'id_dependencia' => 4, // Obra
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'David',
                'apellido' => 'Rojas',
                'email' => 'david.rojas@coreprojects.com',
                'password' => bcrypt('password123'),
                'telefono' => '3012345678',
                'id_cargo' => 2, // Ingeniero de Obra
                'id_dependencia' => 4, // Obra
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // Compras
            [
                'nombre' => 'Elena',
                'apellido' => 'Mendoza',
                'email' => 'elena.mendoza@coreprojects.com',
                'password' => bcrypt('password123'),
                'telefono' => '3013456789',
                'id_cargo' => 5, // Jefe de Compras
                'id_dependencia' => 5, // Compras
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'Raúl',
                'apellido' => 'Ortiz',
                'email' => 'raul.ortiz@coreprojects.com',
                'password' => bcrypt('password123'),
                'telefono' => '3014567890',
                'id_cargo' => 3, // Comprador
                'id_dependencia' => 5, // Compras
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'Patricia',
                'apellido' => 'Silva',
                'email' => 'patricia.silva@coreprojects.com',
                'password' => bcrypt('password123'),
                'telefono' => '3015678901',
                'id_cargo' => 4, // Auxiliar de Compras
                'id_dependencia' => 5, // Compras
                'estado' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // Empleados inactivos (para pruebas)
            [
                'nombre' => 'Andrés',
                'apellido' => 'Pérez',
                'email' => 'andres.perez@coreprojects.com',
                'password' => bcrypt('password123'),
                'telefono' => '3016789012',
                'id_cargo' => 4, // Asesor Comercial
                'id_dependencia' => 2, // Comercial
                'estado' => false, // Inactivo
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'Verónica',
                'apellido' => 'Gutiérrez',
                'email' => 'veronica.gutierrez@coreprojects.com',
                'password' => bcrypt('password123'),
                'telefono' => '3017890123',
                'id_cargo' => 3, // Auxiliar Contable
                'id_dependencia' => 3, // Contabilidad
                'estado' => false, // Inactivo
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
