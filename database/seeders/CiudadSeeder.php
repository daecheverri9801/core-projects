<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CiudadSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Leticia',
            'codigo_postal' => '910001',
            'id_departamento' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Medellín',
            'codigo_postal' => '050001',
            'id_departamento' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Arauca',
            'codigo_postal' => '810001',
            'id_departamento' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Barranquilla',
            'codigo_postal' => '080001',
            'id_departamento' => 4,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Cartagena',
            'codigo_postal' => '130001',
            'id_departamento' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Tunja',
            'codigo_postal' => '150001',
            'id_departamento' => 6,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Manizales',
            'codigo_postal' => '170001',
            'id_departamento' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Florencia',
            'codigo_postal' => '180001',
            'id_departamento' => 8,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Yopal',
            'codigo_postal' => '850001',
            'id_departamento' => 9,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Popayán',
            'codigo_postal' => '190001',
            'id_departamento' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Valledupar',
            'codigo_postal' => '200001',
            'id_departamento' => 11,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Quibdó',
            'codigo_postal' => '270001',
            'id_departamento' => 12,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Montería',
            'codigo_postal' => '230001',
            'id_departamento' => 13,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Bogotá D.C.',
            'codigo_postal' => '110111',
            'id_departamento' => 14,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Inírida',
            'codigo_postal' => '940001',
            'id_departamento' => 15,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'San José del Guaviare',
            'codigo_postal' => '950001',
            'id_departamento' => 16,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Neiva',
            'codigo_postal' => '410001',
            'id_departamento' => 17,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Riohacha',
            'codigo_postal' => '440001',
            'id_departamento' => 18,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Santa Marta',
            'codigo_postal' => '470001',
            'id_departamento' => 19,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Villavicencio',
            'codigo_postal' => '500001',
            'id_departamento' => 20,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Pasto',
            'codigo_postal' => '520001',
            'id_departamento' => 21,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Cúcuta',
            'codigo_postal' => '540001',
            'id_departamento' => 22,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Mocoa',
            'codigo_postal' => '860001',
            'id_departamento' => 23,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Armenia',
            'codigo_postal' => '630001',
            'id_departamento' => 24,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Pereira',
            'codigo_postal' => '660001',
            'id_departamento' => 25,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'San Andrés',
            'codigo_postal' => '880001',
            'id_departamento' => 26,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Bucaramanga',
            'codigo_postal' => '680001',
            'id_departamento' => 27,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Sincelejo',
            'codigo_postal' => '700001',
            'id_departamento' => 28,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Ibagué',
            'codigo_postal' => '730001',
            'id_departamento' => 29,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Cali',
            'codigo_postal' => '760001',
            'id_departamento' => 30,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Mitú',
            'codigo_postal' => '970001',
            'id_departamento' => 31,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Puerto Carreño',
            'codigo_postal' => '990001',
            'id_departamento' => 32,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Bogotá D.C.',
            'codigo_postal' => '110111',
            'id_departamento' => 33,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Caldas
        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Aguadas',
            'codigo_postal' => '172020',
            'id_departamento' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Anserma',
            'codigo_postal' => '177080',
            'id_departamento' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Aranzazu',
            'codigo_postal' => '172010',
            'id_departamento' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Belalcázar',
            'codigo_postal' => '177040',
            'id_departamento' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Chinchiná',
            'codigo_postal' => '176020',
            'id_departamento' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Filadelfia',
            'codigo_postal' => '177001',
            'id_departamento' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'La Dorada',
            'codigo_postal' => '175030',
            'id_departamento' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'La Merced',
            'codigo_postal' => '177060',
            'id_departamento' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Manzanares',
            'codigo_postal' => '174010',
            'id_departamento' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Marmato',
            'codigo_postal' => '177020',
            'id_departamento' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Marquetalia',
            'codigo_postal' => '174020',
            'id_departamento' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Marulanda',
            'codigo_postal' => '172040',
            'id_departamento' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Neira',
            'codigo_postal' => '175047',
            'id_departamento' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Norcasia',
            'codigo_postal' => '175050',
            'id_departamento' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Pacora',
            'codigo_postal' => '172001',
            'id_departamento' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Palestina',
            'codigo_postal' => '175017',
            'id_departamento' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Pensilvania',
            'codigo_postal' => '174030',
            'id_departamento' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Riosucio',
            'codigo_postal' => '178040',
            'id_departamento' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Risaralda',
            'codigo_postal' => '176040',
            'id_departamento' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Salamina',
            'codigo_postal' => '172030',
            'id_departamento' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Samaná',
            'codigo_postal' => '174001',
            'id_departamento' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'San José',
            'codigo_postal' => '177030',
            'id_departamento' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Supía',
            'codigo_postal' => '178060',
            'id_departamento' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Victoria',
            'codigo_postal' => '175040',
            'id_departamento' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Villamaría',
            'codigo_postal' => '170004',
            'id_departamento' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Viterbo',
            'codigo_postal' => '177060',
            'id_departamento' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Risaralda
        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Apía',
            'codigo_postal' => '663001',
            'id_departamento' => 25,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Balboa',
            'codigo_postal' => '663040',
            'id_departamento' => 25,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Belén de Umbría',
            'codigo_postal' => '664001',
            'id_departamento' => 25,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Dosquebradas',
            'codigo_postal' => '661001',
            'id_departamento' => 25,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Guática',
            'codigo_postal' => '664020',
            'id_departamento' => 25,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'La Celia',
            'codigo_postal' => '663008',
            'id_departamento' => 25,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'La Virginia',
            'codigo_postal' => '662001',
            'id_departamento' => 25,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Marsella',
            'codigo_postal' => '663001',
            'id_departamento' => 25,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Mistrató',
            'codigo_postal' => '665001',
            'id_departamento' => 25,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Pueblo Rico',
            'codigo_postal' => '665001',
            'id_departamento' => 25,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Quinchía',
            'codigo_postal' => '664001',
            'id_departamento' => 25,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Santa Rosa de Cabal',
            'codigo_postal' => '661020',
            'id_departamento' => 25,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Santuario',
            'codigo_postal' => '663070',
            'id_departamento' => 25,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Quindío
        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Buenavista',
            'codigo_postal' => '632001',
            'id_departamento' => 24,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Calarcá',
            'codigo_postal' => '632001',
            'id_departamento' => 24,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Circasia',
            'codigo_postal' => '631001',
            'id_departamento' => 24,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Córdoba',
            'codigo_postal' => '632007',
            'id_departamento' => 24,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Filandia',
            'codigo_postal' => '634001',
            'id_departamento' => 24,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Génova',
            'codigo_postal' => '633001',
            'id_departamento' => 24,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'La Tebaida',
            'codigo_postal' => '633020',
            'id_departamento' => 24,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Montenegro',
            'codigo_postal' => '633001',
            'id_departamento' => 24,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Pijao',
            'codigo_postal' => '632060',
            'id_departamento' => 24,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Quimbaya',
            'codigo_postal' => '634020',
            'id_departamento' => 24,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->firstOrCreate([
            'nombre' => 'Salento',
            'codigo_postal' => '631020',
            'id_departamento' => 24,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
