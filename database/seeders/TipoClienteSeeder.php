<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoCliente;

class TipoClienteSeeder extends Seeder
{
    public function run()
    {
        $tipos = [
            ['tipo_cliente' => 'Persona Natural', 'descripcion' => 'Individuo particular.'],
            ['tipo_cliente' => 'Persona Jurídica', 'descripcion' => 'Empresa o sociedad.'],
            ['tipo_cliente' => 'Constructor', 'descripcion' => 'Empresa constructora.'],
            ['tipo_cliente' => 'Inversionista', 'descripcion' => 'Persona que compra para invertir.'],
            ['tipo_cliente' => 'Gobierno', 'descripcion' => 'Entidad gubernamental.'],
            ['tipo_cliente' => 'Extranjero', 'descripcion' => 'Cliente no residente en el país.'],
        ];

        foreach ($tipos as $tipo) {
            TipoCliente::firstOrCreate($tipo);
        }
    }
}