<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PaisSeeder::class,
            DepartamentoSeeder::class,
            CiudadSeeder::class,
            EstadoSeeder::class,
            EstadoInmuebleSeeder::class,
            UbicacionSeeder::class,
            ProyectoSeeder::class,
            TorreSeeder::class,
            PisoTorreSeeder::class,
            TipoApartamentoSeeder::class,
            ApartamentoSeeder::class,
            LocalSeeder::class,
            ParqueaderoSeeder::class,
            ZonaSocialSeeder::class,
            PoliticaComisionSeeder::class,
            PoliticaPrecioProyectoSeeder::class,
            CargoSeeder::class,
            DependenciaSeeder::class,
            EmpleadoSeeder::class,
        ]);
    }
}
