<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use App\Models\Proyecto;
use App\Models\EstadoInmueble;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\GerenciaEstadisticasService;

class InventarioController extends Controller
{
    public function index(Request $request, GerenciaEstadisticasService $service)
    {
        $empleado = $request->user()->load('cargo');

        $filtros = [
            'desde'           => $request->query('desde'),
            'hasta'           => $request->query('hasta'),
            'proyecto_id'     => $request->query('proyecto_id'),
            'asesor_id'       => null,
            'estado_inmueble' => $request->query('estado_inmueble'),
            'q'               => $request->query('q'),
            'tipo'            => $request->query('tipo'),
        ];

        $dashboard = $service->obtenerDashboard([
            'desde'           => $filtros['desde'],
            'hasta'           => $filtros['hasta'],
            'proyecto_id'     => $filtros['proyecto_id'],
            'asesor_id'       => null,
            'estado_inmueble' => $filtros['estado_inmueble'],
        ]);

        return Inertia::render('Contabilidad/Inventario/Inventario', [
            'inventarioProyectos' => $dashboard['inventarioProyectos'] ?? [],
            'proyectos'           => Proyecto::orderBy('nombre')->get(['id_proyecto', 'nombre']),
            'estadosInmueble'     => EstadoInmueble::orderBy('nombre')->get(['id_estado_inmueble', 'nombre']),
            'filtros'             => $filtros,
            'empleado'            => $empleado,
        ]);
    }
}
