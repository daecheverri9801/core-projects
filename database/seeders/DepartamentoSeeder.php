<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // ← necesario para DB
use App\Models\Pais;

class DepartamentoSeeder extends Seeder
{
    public function run()
    {
        // Obtener o crear el país Colombia (esto sigue usando el modelo Pais, pero es seguro)
        // $colombia = Pais::firstOrCreate(['nombre' => 'Colombia']);

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
            DB::table('departamentos')->insertOrIgnore([
                'nombre'      => $nombre,
                'id_pais'     => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }
}
