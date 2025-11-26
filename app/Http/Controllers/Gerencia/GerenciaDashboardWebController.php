<?php

namespace App\Http\Controllers\Gerencia;

use App\Http\Controllers\Controller;
use App\Models\Proyecto;
use App\Models\Empleado;
use App\Models\EstadoInmueble;
use App\Services\GerenciaEstadisticasService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GerenciaDashboardWebController extends Controller
{
    public function index(Request $request, GerenciaEstadisticasService $service)
    {
        $filtros = [
            'desde'           => $request->query('desde'),
            'hasta'           => $request->query('hasta'),
            'proyecto_id'     => $request->query('proyecto_id'),
            'asesor_id'       => $request->query('asesor_id'),
            'estado_inmueble' => $request->query('estado_inmueble'),
        ];

        $dashboard = $service->obtenerDashboard($filtros);

        $proyectos = Proyecto::select('id_proyecto', 'nombre')
            ->orderBy('nombre')
            ->get();

        $empleados = Empleado::select('id_empleado', 'nombre', 'apellido')
            ->orderBy('nombre')
            ->get();

        $estados = EstadoInmueble::select('id_estado_inmueble', 'nombre')
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Gerencia/Dashboard/Index', array_merge($dashboard, [
            'proyectos'       => $proyectos,
            'empleados'       => $empleados,
            'estadosInmueble' => $estados,
            'filtros'         => $filtros,
        ]));
    }
}
