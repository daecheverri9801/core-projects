<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cargo;

class CargoSeeder extends Seeder
{
    public function run()
    {
        $cargos = [
            ['nombre' => 'Gerente', 'descripcion' => 'Máximo responsable de la empresa.'],
            ['nombre' => 'Administrador', 'descripcion' => 'Administrador General del Sistema'],
            ['nombre' => 'Subgerente', 'descripcion' => 'Apoyo en la dirección general.'],
            ['nombre' => 'Director de Proyectos', 'descripcion' => 'Coordina todos los proyectos.'],
            ['nombre' => 'Asesor Comercial', 'descripcion' => 'Captación de clientes y cierre de ventas.'],
            ['nombre' => 'Jefe de Marketing', 'descripcion' => 'Estrategias de promoción.'],
            ['nombre' => 'Analista Financiero', 'descripcion' => 'Evaluación financiera de proyectos.'],
            ['nombre' => 'Contador', 'descripcion' => 'Gestión contable y tributaria.'],
            ['nombre' => 'Abogado', 'descripcion' => 'Asesoría legal y redacción de contratos.'],
            ['nombre' => 'Auxiliar Administrativo', 'descripcion' => 'Soporte en labores administrativas.'],
            ['nombre' => 'Coordinador de Compras', 'descripcion' => 'Gestión de adquisiciones.'],
            ['nombre' => 'Directora Comercial', 'descripcion' => 'Liderazgo en estrategias de venta.'],
            ['nombre' => 'Asesora Comercial', 'descripcion' => 'Captación de clientes y cierre de ventas.'],
        ];

        foreach ($cargos as $cargo) {
            Cargo::firstOrCreate($cargo);
        }
    }
}