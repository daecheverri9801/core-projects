<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            PaisSeeder::class,
            DepartamentoSeeder::class,
            CiudadSeeder::class,
            DependenciaSeeder::class,
            CargoSeeder::class,
            EmpleadoSeeder::class,
            EstadoSeeder::class,
            EstadoInmuebleSeeder::class,
            EstadoVentaSeeder::class,
            FormaPagoSeeder::class,
            MedioPagoSeeder::class,
            TipoClienteSeeder::class,
            TipoDocumentoSeeder::class,
        ]);
    }
}
