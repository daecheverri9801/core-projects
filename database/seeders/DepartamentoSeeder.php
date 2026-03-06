<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pais;
use App\Models\Departamento;

class DepartamentoSeeder extends Seeder
{
    public function run()
    {
        // Obtener o crear el país Colombia
        $colombia = Pais::firstOrCreate(['nombre' => 'Colombia']);

        $departamentos = [
            'Amazonas',
            'Antioquia',
            'Arauca',
            'Atlántico',
            'Bolívar',
            'Boyacá',
            'Caldas',
            'Caquetá',
            'Casanare',
            'Cauca',
            'Cesar',
            'Chocó',
            'Córdoba',
            'Cundinamarca',
            'Guainía',
            'Guaviare',
            'Huila',
            'La Guajira',
            'Magdalena',
            'Meta',
            'Nariño',
            'Norte de Santander',
            'Putumayo',
            'Quindío',
            'Risaralda',
            'San Andrés y Providencia',
            'Santander',
            'Sucre',
            'Tolima',
            'Valle del Cauca',
            'Vaupés',
            'Vichada',
            'Bogotá D.C.'
        ];

        foreach ($departamentos as $nombre) {
            Departamento::firstOrCreate([
                'nombre' => $nombre,
                'id_pais' => $colombia->id,  // ahora $colombia siempre existe
            ]);
        }
    }
}
