<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EstadoInmueble;

class EstadoInmuebleSeeder extends Seeder
{
    public function run()
    {
        $estados = [
            ['nombre' => 'Disponible', 'descripcion' => 'Inmueble a la venta sin ninguna reserva.'],
            ['nombre' => 'Separado', 'descripcion' => 'Cliente ha hecho una separación con dinero no reembolsable.'],
            ['nombre' => 'Reservado', 'descripcion' => 'Inmueble apartado provisionalmente.'],
            ['nombre' => 'Vendido', 'descripcion' => 'Venta formalizada y escriturada.'],
            ['nombre' => 'Bloqueado', 'descripcion' => 'No disponible para la venta (ej: usado por la constructora).'],
            ['nombre' => 'Congelado', 'descripcion' => 'Inmueble fuera de comercialización temporalmente.'],
            ['nombre' => 'En Negociación', 'descripcion' => 'En proceso de negociación con un cliente.'],
        ];

        foreach ($estados as $estado) {
            EstadoInmueble::firstOrCreate($estado);
        }
    }
}