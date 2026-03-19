<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use App\Models\Proyecto;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\GerenciaEstadisticasService;

class ComisionController extends Controller
{
    public function index(Request $request, GerenciaEstadisticasService $service)
    {
        $empleado = $request->user()->load('cargo');

        $filtros = [
            'desde'           => $request->query('desde'),
            'hasta'           => $request->query('hasta'),
            'proyecto_id'     => $request->query('proyecto_id'),
            'asesor_id'       => null,
            'estado_inmueble' => null,
            'q'               => $request->query('q'),
        ];

        $dashboard = $service->obtenerDashboard([
            'desde'           => $filtros['desde'],
            'hasta'           => $filtros['hasta'],
            'proyecto_id'     => $filtros['proyecto_id'],
            'asesor_id'       => null,
            'estado_inmueble' => null,
        ]);

        return Inertia::render('Contabilidad/Comisiones/Consolidado', [
            'consolidadoComisiones' => $dashboard['consolidadoComisiones'] ?? [],
            'proyectos'             => Proyecto::orderBy('nombre')->get(['id_proyecto', 'nombre']),
            'filtros'               => $filtros,
            'empleado'              => $empleado,
        ]);
    }
}
