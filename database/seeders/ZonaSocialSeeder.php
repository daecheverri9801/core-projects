<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ZonaSocialSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('zonas_sociales')->insert([
            ['nombre' => 'Piscina Principal', 'descripcion' => 'Piscina semi-olímpica con zona de hidromasaje', 'id_proyecto' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Gimnasio', 'descripcion' => 'Gimnasio equipado con máquinas de última generación', 'id_proyecto' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Salón Social', 'descripcion' => 'Salón de eventos con capacidad para 80 personas', 'id_proyecto' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Zona BBQ', 'descripcion' => 'Área de parrillas con mesas y sillas', 'id_proyecto' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Parque Infantil', 'descripcion' => 'Zona de juegos para niños', 'id_proyecto' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Cancha Múltiple', 'descripcion' => 'Cancha para fútbol, baloncesto y voleibol', 'id_proyecto' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Coworking', 'descripcion' => 'Espacio de trabajo compartido', 'id_proyecto' => 3, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}