<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\Proyecto;
use App\Services\GerenciaEstadisticasService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Exports\PlanPagosCIExport;
use Maatwebsite\Excel\Facades\Excel;

class ContabilidadVentasWebController extends Controller
{
    public function index(Request $request)
    {
        $empleado = $request->user()->load('cargo');

        $proyectoId = $request->query('proyecto_id'); // null o id

        $ventasQuery = Venta::with([
            'cliente',
            'empleado',
            'apartamento.estadoInmueble',
            'local.estadoInmueble',
            'proyecto',
            'formaPago',
        ])->orderBy('fecha_venta', 'desc');

        if (!empty($proyectoId)) {
            $ventasQuery->where('id_proyecto', $proyectoId);
        }

        $ventas = $ventasQuery->get();

        $proyectos = Proyecto::select('id_proyecto', 'nombre')
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Contabilidad/Ventas/Index', [
            'ventas' => $ventas,
            'proyectos' => $proyectos,
            'filtros' => [
                'proyecto_id' => $proyectoId ?: '',
            ],
            'empleado' => $empleado,
        ]);
    }

    public function show(Request $request, $id)
    {
        $empleado = $request->user()->load('cargo');

        $venta = Venta::with([
            'cliente',
            'empleado',
            'proyecto',
            'formaPago',

            'proyecto.ubicacion',
            'proyecto.zonasSociales',

            'apartamento.estadoInmueble',
            'apartamento.torre',
            'apartamento.pisoTorre',
            'apartamento.tipoApartamento',
            'apartamento.parqueaderos',

            'local.estadoInmueble',
            'local.torre',
            'local.pisoTorre',

            'planAmortizacion.cuotas',
            'pagos',
        ])->findOrFail($id);

        $imagenTipoAptoUrl = null;
        if ($venta->apartamento?->tipoApartamento?->imagen) {
            $imagenTipoAptoUrl = asset('storage/' . $venta->apartamento->tipoApartamento->imagen);
        }

        return Inertia::render('Contabilidad/Ventas/Show', [
            'venta' => $venta,
            'imagenTipoAptoUrl' => $imagenTipoAptoUrl,
            'empleado' => $empleado,
        ]);
    }


    /**
     * Consolidado Plan de Pagos CI (personalizable por mes o rango)
     * - query: ano, mes (opcional), desde, hasta, proyecto_id, asesor_id
     */
    public function planPagosCI(Request $request, GerenciaEstadisticasService $service)
    {
        $empleado = $request->user()->load('cargo');

        $ano = (int) $request->query('ano', now()->year);
        $mes = $request->query('mes'); // 1..12 o null
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        // Si elige mes y no viene rango manual => construir rango del mes
        if ($mes && (!$desde || !$hasta)) {
            $m = (int) $mes;
            $desde = now()->setYear($ano)->setMonth($m)->startOfMonth()->toDateString();
            $hasta = now()->setYear($ano)->setMonth($m)->endOfMonth()->toDateString();
        }

        $filtros = [
            'desde'       => $desde,
            'hasta'       => $hasta,
            'proyecto_id' => $request->query('proyecto_id'),
            'asesor_id'   => $request->query('asesor_id'),
        ];

        [$desdeR, $hastaR] = $service->rangoFechas($filtros);
        $plan = $service->planPagosCI($filtros, $desdeR, $hastaR);

        return Inertia::render('Contabilidad/Reportes/PlanPagosCI', [
            'empleado' => $empleado,
            'planPagosCI' => $plan,
            'proyectos' => Proyecto::select('id_proyecto', 'nombre')->orderBy('nombre')->get(),
            'filtros' => array_merge($filtros, [
                'ano' => $ano,
                'mes' => $mes ? (int)$mes : '',
            ]),
        ]);
    }

    public function exportPlanPagosCI(Request $request, GerenciaEstadisticasService $service)
    {
        $ano = (int) $request->query('ano', now()->year);
        $mes = $request->query('mes');
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        if ($mes && (!$desde || !$hasta)) {
            $m = (int) $mes;
            $desde = now()->setYear($ano)->setMonth($m)->startOfMonth()->toDateString();
            $hasta = now()->setYear($ano)->setMonth($m)->endOfMonth()->toDateString();
        }

        $filtros = [
            'desde'       => $desde,
            'hasta'       => $hasta,
            'proyecto_id' => $request->query('proyecto_id'),
            'asesor_id'   => $request->query('asesor_id'),
        ];

        [$desdeR, $hastaR] = $service->rangoFechas($filtros);
        $plan = $service->planPagosCI($filtros, $desdeR, $hastaR);

        $suffix = $mes ? "año{$ano}_mes{$mes}" : "año{$ano}";
        return Excel::download(
            new PlanPagosCIExport($plan['encabezados'], $plan['filas'], $plan['totales']),
            "plan_pagos_cuota_inicial_{$suffix}.xlsx"
        );
    }
}
