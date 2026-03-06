<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Departamento;
use App\Models\Ciudad;

class CiudadSeeder extends Seeder
{
    public function run()
    {
        // Capitales de todos los departamentos (incluye Bogotá)
        $capitales = [
            'Amazonas' => 'Leticia',
            'Antioquia' => 'Medellín',
            'Arauca' => 'Arauca',
            'Atlántico' => 'Barranquilla',
            'Bolívar' => 'Cartagena',
            'Boyacá' => 'Tunja',
            'Caldas' => 'Manizales',     // Luego se agregarán todos los municipios
            'Caquetá' => 'Florencia',
            'Casanare' => 'Yopal',
            'Cauca' => 'Popayán',
            'Cesar' => 'Valledupar',
            'Chocó' => 'Quibdó',
            'Córdoba' => 'Montería',
            'Cundinamarca' => 'Bogotá D.C.',
            'Guainía' => 'Inírida',
            'Guaviare' => 'San José del Guaviare',
            'Huila' => 'Neiva',
            'La Guajira' => 'Riohacha',
            'Magdalena' => 'Santa Marta',
            'Meta' => 'Villavicencio',
            'Nariño' => 'Pasto',
            'Norte de Santander' => 'Cúcuta',
            'Putumayo' => 'Mocoa',
            'Quindío' => 'Armenia',      // Luego se agregarán todos los municipios
            'Risaralda' => 'Pereira',     // Luego se agregarán todos los municipios
            'San Andrés y Providencia' => 'San Andrés',
            'Santander' => 'Bucaramanga',
            'Sucre' => 'Sincelejo',
            'Tolima' => 'Ibagué',
            'Valle del Cauca' => 'Cali',
            'Vaupés' => 'Mitú',
            'Vichada' => 'Puerto Carreño',
            'Bogotá D.C.' => 'Bogotá D.C.',
        ];

        // Insertar capitales
        foreach ($capitales as $depNombre => $ciudadNombre) {
            $departamento = Departamento::where('nombre', $depNombre)->first();
            if ($departamento) {
                Ciudad::firstOrCreate([
                    'nombre' => $ciudadNombre,
                    'codigo_postal' => '000000', // Código postal genérico
                    'id_departamento' => $departamento->id,
                ]);
            }
        }

        // Municipios completos para Caldas, Risaralda y Quindío
        $municipiosExtra = [
            'Caldas' => [
                'Manizales', 'Aguadas', 'Anserma', 'Aranzazu', 'Belalcázar', 'Chinchiná',
                'Filadelfia', 'La Dorada', 'La Merced', 'Manzanares', 'Marmato', 'Marquetalia',
                'Marulanda', 'Neira', 'Norcasia', 'Pacora', 'Palestina', 'Pensilvania',
                'Riosucio', 'Risaralda', 'Salamina', 'Samaná', 'San José', 'Supía',
                'Victoria', 'Villamaría', 'Viterbo'
            ],
            'Risaralda' => [
                'Pereira', 'Apía', 'Balboa', 'Belén de Umbría', 'Dosquebradas', 'Guática',
                'La Celia', 'La Virginia', 'Marsella', 'Mistrató', 'Pueblo Rico', 'Quinchía',
                'Santa Rosa de Cabal', 'Santuario'
            ],
            'Quindío' => [
                'Armenia', 'Buenavista', 'Calarcá', 'Circasia', 'Córdoba', 'Filandia',
                'Génova', 'La Tebaida', 'Montenegro', 'Pijao', 'Quimbaya', 'Salento'
            ],
        ];

        foreach ($municipiosExtra as $depNombre => $ciudades) {
            $departamento = Departamento::where('nombre', $depNombre)->first();
            if ($departamento) {
                foreach ($ciudades as $ciudadNombre) {
                    Ciudad::firstOrCreate([
                        'nombre' => $ciudadNombre,
                        'codigo_postal' => '000000',
                        'id_departamento' => $departamento->id,
                    ]);
                }
            }
        }
    }
}