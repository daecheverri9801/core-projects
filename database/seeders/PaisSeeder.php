<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaisSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('paises')->insert([
            ['nombre' => 'Colombia', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'México', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Argentina', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Chile', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Perú', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}