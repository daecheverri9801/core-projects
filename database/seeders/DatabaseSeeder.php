<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Seeders del módulo de Ventas
        $this->call([
            TipoClienteSeeder::class,
            TipoDocumentoSeeder::class,
            ClienteSeeder::class,
            FormaPagoSeeder::class,
            EstadoVentaSeeder::class,
            ConceptoPagoSeeder::class,
            MedioPagoSeeder::class,
            VentaSeeder::class,
            PlanAmortizacionVentaSeeder::class,
            PlanAmortizacionCuotaSeeder::class,
            PagoSeeder::class,
        ]);
    }
}
