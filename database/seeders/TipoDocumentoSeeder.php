<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoDocumentoSeeder extends Seeder
{
    public function run()
    {
        DB::table('tipos_documento')->insert([
            [
                'tipo_documento' => 'Cédula de Ciudadanía',
                'descripcion' => 'Documento de identidad para ciudadanos colombianos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo_documento' => 'Cédula de Extranjería',
                'descripcion' => 'Documento de identidad para extranjeros residentes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo_documento' => 'NIT',
                'descripcion' => 'Número de Identificación Tributaria',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipo_documento' => 'Pasaporte',
                'descripcion' => 'Documento de viaje internacional',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
