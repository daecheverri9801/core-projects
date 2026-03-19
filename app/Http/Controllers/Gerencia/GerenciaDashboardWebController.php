<?php

namespace App\Http\Controllers\Gerencia;

use App\Http\Controllers\Controller;
use App\Models\Proyecto;
use App\Models\Empleado;
use App\Models\EstadoInmueble;
use App\Models\Cargo;
use App\Services\GerenciaEstadisticasService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Exports\PlanPagosCIExport;
use Maatwebsite\Excel\Facades\Excel;

class GerenciaDashboardWebController extends Controller
{
    public function index(Request $request, GerenciaEstadisticasService $service)
    {
        $empleado = $request->user()->load('cargo');
        $filtros = [
            'desde'           => $request->query('desde'),
            'hasta'           => $request->query('hasta'),
            'proyecto_id'     => $request->query('proyecto_id'),
            'asesor_id'       => $request->query('asesor_id'),
            'estado_inmueble' => $request->query('estado_inmueble'),
        ];

        $cargo = Cargo::whereIn('nombre',  ['Directora Comercial', 'Asesora Comercial'])->get();

        $Mempleados = $cargo->isNotEmpty()
            ? Empleado::whereIn('id_cargo', $cargo->pluck('id_cargo'))
            ->select('id_empleado', 'nombre', 'apellido')
            ->get() : collect();

        [$desde, $hasta] = $service->rangoFechas($filtros);

        $planPagosCI = $service->planPagosCI($filtros, $desde, $hasta);
        $dashboard = $service->obtenerDashboard($filtros);

        return Inertia::render('Gerencia/Dashboard/Index', array_merge($dashboard, [
            'proyectos'       => Proyecto::orderBy('nombre')->get(),
            'empleados'       => $Mempleados,
            'estadosInmueble' => EstadoInmueble::orderBy('nombre')->get(),
            'filtros'         => $filtros,
            'planPagosCI'     => $planPagosCI,
            'empleado' => $empleado,
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
