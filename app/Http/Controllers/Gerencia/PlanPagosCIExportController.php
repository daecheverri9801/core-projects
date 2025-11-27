<?php

namespace App\Http\Controllers\Gerencia;

use App\Http\Controllers\Controller;
use App\Services\GerenciaEstadisticasService;
use Illuminate\Http\Request;
use App\Exports\PlanPagosCIExport;
use Maatwebsite\Excel\Facades\Excel;

class PlanPagosCIExportController extends Controller
{
    public function export(Request $request, GerenciaEstadisticasService $service)
    {
        // Filtros coherentes con el dashboard
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
