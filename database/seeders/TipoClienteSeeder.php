<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoClienteSeeder extends Seeder
{
    public function run()
    {
        DB::table('tipos_cliente')->insert([
            [
                'tipo_cliente' => 'Persona Natural',
                'descripcion' => 'Cliente individual o persona física',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo_cliente' => 'Persona Jurídica',
                'descripcion' => 'Empresa o entidad legal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo_cliente' => 'Inversionista',
                'descripcion' => 'Cliente que compra con fines de inversión',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo_cliente' => 'Corporativo',
                'descripcion' => 'Grandes empresas o corporaciones',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
