<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pais;

class PaisSeeder extends Seeder
{
    public function run()
    {
        $paises = [
            ['nombre' => 'Colombia'],
            ['nombre' => 'Argentina'],
            ['nombre' => 'Bolivia'],
            ['nombre' => 'Brasil'],
            ['nombre' => 'Chile'],
            ['nombre' => 'Ecuador'],
            ['nombre' => 'Guyana'],
            ['nombre' => 'Paraguay'],
            ['nombre' => 'Perú'],
            ['nombre' => 'Surinam'],
            ['nombre' => 'Uruguay'],
            ['nombre' => 'Venezuela'],
        ];

        foreach ($paises as $pais) {
            Pais::firstOrCreate($pais);
        }
    }
}
