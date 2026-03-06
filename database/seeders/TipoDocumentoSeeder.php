<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoDocumento;

class TipoDocumentoSeeder extends Seeder
{
    public function run()
    {
        $tipos = [
            ['tipo_documento' => 'Cédula de Ciudadanía', 'descripcion' => 'Documento de identidad colombiano.'],
            ['tipo_documento' => 'Cédula de Extranjería', 'descripcion' => 'Identificación para residentes extranjeros.'],
            ['tipo_documento' => 'Pasaporte', 'descripcion' => 'Documento de viaje internacional.'],
            ['tipo_documento' => 'NIT', 'descripcion' => 'Número de Identificación Tributaria para empresas.'],
            ['tipo_documento' => 'Tarjeta de Identidad', 'descripcion' => 'Documento para menores de edad.'],
            ['tipo_documento' => 'Registro Civil', 'descripcion' => 'Registro de nacimiento.'],
            ['tipo_documento' => 'PEP / PPT', 'descripcion' => 'Permiso de Permanencia o Protección Temporal.'],
        ];

        foreach ($tipos as $tipo) {
            TipoDocumento::firstOrCreate($tipo);
        }
    }
}