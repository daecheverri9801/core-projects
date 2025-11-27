<?php

namespace App\Http\Controllers\Gerencia;

use App\Http\Controllers\Controller;
use App\Models\Proyecto;
use App\Models\Empleado;
use App\Models\EstadoInmueble;
use App\Services\GerenciaEstadisticasService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Exports\PlanPagosCIExport;
use Maatwebsite\Excel\Facades\Excel;

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

        [$desde, $hasta] = $service->rangoFechas($filtros);

        $planPagosCI = $service->planPagosCI($filtros, $desde, $hasta);
        $dashboard = $service->obtenerDashboard($filtros);

        return Inertia::render('Gerencia/Dashboard/Index', array_merge($dashboard, [
            'proyectos'       => Proyecto::orderBy('nombre')->get(),
            'empleados'       => Empleado::orderBy('nombre')->get(),
            'estadosInmueble' => EstadoInmueble::orderBy('nombre')->get(),
            'filtros'         => $filtros,
            'planPagosCI'     => $planPagosCI
        ]));
    }

    public function exportPlanPagosCI(Request $request, GerenciaEstadisticasService $service)
    {
        $filtros = [
            'desde'       => $request->query('desde'),
            'hasta'       => $request->query('hasta'),
            'proyecto_id' => $request->query('proyecto_id'),
            'asesor_id'   => $request->query('asesor_id'),
        ];

        [$desde, $hasta] = $service->rangoFechas($filtros);
        $plan = $service->planPagosCI($filtros, $desde, $hasta);

        return Excel::download(
            new PlanPagosCIExport($plan['encabezados'], $plan['filas'], $plan['totales']),
            'plan_pagos_cuota_inicial.xlsx'
        );
    }
}
