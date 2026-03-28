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
use Carbon\Carbon;

class ContabilidadVentasWebController extends Controller
{
    public function index(Request $request)
    {
        $empleado = $request->user()->load('cargo');

        $proyectoId = $request->query('id_proyecto');

        $ventasQuery = Venta::with([
            'cliente',
            'empleado',
            'apartamento.estadoInmueble',
            'apartamento.torre',
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
                'id_proyecto' => $proyectoId ?: '',
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
            'parqueadero',
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
     * Consolidado Plan de Pagos CI
     *
     * Reglas:
     * - Si vienen desde/hasta, se usa rango manual
     * - Si no vienen fechas manuales y viene año + mes, se filtra ese mes
     * - Si no vienen fechas manuales y solo viene año, se filtra ese año
     */
    public function planPagosCI(Request $request, GerenciaEstadisticasService $service)
    {
        $empleado = $request->user()->load('cargo');

        $ano = $request->query('ano');
        $mes = $request->query('mes');
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        [$desdeR, $hastaR] = $this->resolverRangoPlanPagos($ano, $mes, $desde, $hasta);

        $filtros = [
            'ano' => $ano !== null && $ano !== '' ? (int) $ano : '',
            'mes' => $mes !== null && $mes !== '' ? (int) $mes : '',
            'desde' => $desde ?: '',
            'hasta' => $hasta ?: '',
            'id_proyecto' => $request->query('id_proyecto', ''),
            'asesor_id' => $request->query('asesor_id', ''),
            'buscar_cliente' => $request->query('buscar_cliente', ''),
        ];

        $plan = $service->planPagosCI($filtros, $desdeR, $hastaR);

        return Inertia::render('Contabilidad/Reportes/PlanPagosCI', [
            'empleado'    => $empleado,
            'planPagosCI' => $plan,
            'proyectos'   => Proyecto::select('id_proyecto', 'nombre')
                ->orderBy('nombre')
                ->get(),
            'filtros'     => $filtros,
        ]);
    }

    public function exportPlanPagosCI(Request $request, GerenciaEstadisticasService $service)
    {
        $ano = $request->query('ano');
        $mes = $request->query('mes');
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');
        $proyectoId = $request->query('id_proyecto');

        [$desdeR, $hastaR] = $this->resolverRangoPlanPagos($ano, $mes, $desde, $hasta);

        $filtros = [
            'ano' => $ano !== null && $ano !== '' ? (int) $ano : '',
            'mes' => $mes !== null && $mes !== '' ? (int) $mes : '',
            'desde' => $desde ?: '',
            'hasta' => $hasta ?: '',
            'id_proyecto' => $request->query('id_proyecto', ''),
            'asesor_id' => $request->query('asesor_id', ''),
            'buscar_cliente' => $request->query('buscar_cliente', ''),
        ];

        $plan = $service->planPagosCI($filtros, $desdeR, $hastaR);

        $proyectoInfo = '';
        if ($proyectoId) {
            $proyecto = Proyecto::find($proyectoId);
            if ($proyecto) {
                $nombreProyecto = preg_replace('/[^a-zA-Z0-9_-]/', '_', $proyecto->nombre);
                $proyectoInfo = "_{$nombreProyecto}";
            }
        }

        $fechaActual = now()->format('d-m-Y');
        $nombreArchivo = "Plan_Pagos_CI_{$fechaActual}{$proyectoInfo}.xlsx";

        return Excel::download(
            new PlanPagosCIExport(
                $plan['encabezados'],
                $plan['filas'],
                $plan['totales']
            ),
            $nombreArchivo
        );
    }

    /**
     * Resuelve el rango de fechas final que debe usar el reporte.
     *
     * Prioridad:
     * 1. Rango manual (desde/hasta)
     * 2. Año + mes
     * 3. Solo año
     */
    private function resolverRangoPlanPagos($ano, $mes, ?string $desde, ?string $hasta): array
    {
        $desdeDate = $desde ? Carbon::parse($desde)->startOfDay() : null;
        $hastaDate = $hasta ? Carbon::parse($hasta)->endOfDay() : null;

        // 1. Rango manual completo
        if ($desdeDate && $hastaDate) {
            return [$desdeDate, $hastaDate];
        }

        // 2. Solo desde
        if ($desdeDate && !$hastaDate) {
            return [$desdeDate, null];
        }

        // 3. Solo hasta
        if (!$desdeDate && $hastaDate) {
            return [null, $hastaDate];
        }

        // 4. Año + mes
        if ($ano !== null && $ano !== '' && $mes !== null && $mes !== '') {
            $base = Carbon::createFromDate((int) $ano, (int) $mes, 1);

            return [
                $base->copy()->startOfMonth()->startOfDay(),
                $base->copy()->endOfMonth()->endOfDay(),
            ];
        }

        // 5. Solo año
        if ($ano !== null && $ano !== '') {
            return [
                Carbon::createFromDate((int) $ano, 1, 1)->startOfDay(),
                Carbon::createFromDate((int) $ano, 12, 31)->endOfDay(),
            ];
        }

        // 6. Sin filtros de fecha => todo el historial
        return [null, null];
    }
}
