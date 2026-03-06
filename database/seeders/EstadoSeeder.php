<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Estado;

class EstadoSeeder extends Seeder
{
    public function run()
    {
        $estados = [
            ['nombre' => 'En Planeación', 'descripcion' => 'Proyecto en fase de planeación y estudios previos.'],
            ['nombre' => 'En Diseño', 'descripcion' => 'Desarrollo de planos y diseños arquitectónicos.'],
            ['nombre' => 'En Ejecución', 'descripcion' => 'Obra en construcción activa.'],
            ['nombre' => 'En Pausa', 'descripcion' => 'Proyecto detenido temporalmente.'],
            ['nombre' => 'Finalizado', 'descripcion' => 'Obra concluida y entregada.'],
            ['nombre' => 'Cancelado', 'descripcion' => 'Proyecto cancelado definitivamente.'],
            ['nombre' => 'En Revisión', 'descripcion' => 'Proyecto en etapa de aprobaciones.'],
        ];

        foreach ($estados as $estado) {
            Estado::firstOrCreate($estado);
        }
    }
}