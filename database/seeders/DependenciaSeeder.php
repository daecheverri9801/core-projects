<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dependencia;

class DependenciaSeeder extends Seeder
{
    public function run()
    {
        $dependencias = [
            ['nombre' => 'Gerencia General', 'descripcion' => 'Dirección y coordinación global de la empresa.'],
            ['nombre' => 'Administrador Plataforma', 'descripcion' => 'Gestión y mantenimiento de la plataforma tecnológica.'],
            ['nombre' => 'Subgerencia', 'descripcion' => 'Apoyo a la gerencia y gestión operativa.'],
            ['nombre' => 'Dirección de Proyectos', 'descripcion' => 'Planificación y supervisión de proyectos constructivos.'],
            ['nombre' => 'Departamento de Construcción', 'descripcion' => 'Ejecución de obras y control de obra.'],
            ['nombre' => 'Departamento de Ventas', 'descripcion' => 'Comercialización de inmuebles.'],
            ['nombre' => 'Departamento de Marketing', 'descripcion' => 'Estrategias de mercado y publicidad.'],
            ['nombre' => 'Recursos Humanos', 'descripcion' => 'Gestión del talento humano y bienestar laboral.'],
            ['nombre' => 'Finanzas', 'descripcion' => 'Administración de recursos económicos y presupuestos.'],
            ['nombre' => 'Contabilidad', 'descripcion' => 'Registro y control contable.'],
            ['nombre' => 'Compras', 'descripcion' => 'Adquisición de materiales e insumos.'],
            ['nombre' => 'Jurídico', 'descripcion' => 'Asesoría legal y gestión de contratos.'],
            ['nombre' => 'Atención al Cliente', 'descripcion' => 'Servicio postventa y atención a clientes.'],
            ['nombre' => 'Postventa', 'descripcion' => 'Seguimiento y garantías después de la entrega.'],
        ];

        foreach ($dependencias as $dep) {
            Dependencia::firstOrCreate($dep);
        }
    }
}