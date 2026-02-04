<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\Proyecto;
use App\Models\Apartamento;
use App\Models\Local;
use App\Models\EstadoInmueble;
use App\Models\Empleado;
use App\Models\Meta;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GerenciaEstadisticasService
{
    /**
     * Punto único para obtener todos los bloques de datos del dashboard
     */
    public function obtenerDashboard(array $filtros): array
    {
        $filtros = $this->normalizarFiltros($filtros);
        [$desde, $hasta] = $this->rangoFechas($filtros);

        return [
            'resumenGlobal'          => $this->resumenGlobal($filtros, $desde, $hasta),
            'ventasPorProyecto'      => $this->ventasPorProyecto($filtros, $desde, $hasta),
            'proyeccionVsReal'       => $this->proyeccionVsRealMensual($filtros, $desde, $hasta),
            'velocidadVentas'        => $this->velocidadVentasPorProyecto($filtros, $desde, $hasta),
            'separacionesEfectiv'    => $this->separacionesYEfectividad($filtros, $desde, $hasta),
            'inventarioProyectos'    => $this->inventarioPorProyecto($filtros),
            'ventasAsesoresProyecto' => $this->ventasPorAsesorProyecto($filtros, $desde, $hasta),

            'estadoInventario'       => $this->estadoInventario(),
            'rankingAsesores'        => $this->rankingAsesores(),
            'absorcionMensual'       => $this->absorcionMensual(),
        ];
    }

    /* ===========================================================
     * NORMALIZACIÓN DE FILTROS (evita '' y castea IDs)
     * =========================================================== */
    private function normalizarFiltros(array $filtros): array
    {
        $out = $filtros;

        $out['desde'] = isset($out['desde']) && is_string($out['desde']) && trim($out['desde']) !== ''
            ? trim($out['desde'])
            : null;

        $out['hasta'] = isset($out['hasta']) && is_string($out['hasta']) && trim($out['hasta']) !== ''
            ? trim($out['hasta'])
            : null;

        $out['proyecto_id'] = $this->toIntOrNull($out['proyecto_id'] ?? null);
        $out['asesor_id'] = $this->toIntOrNull($out['asesor_id'] ?? null);
        $out['estado_inmueble'] = $this->toIntOrNull($out['estado_inmueble'] ?? null);

        return $out;
    }

    private function toIntOrNull($v): ?int
    {
        if ($v === null) return null;
        if (is_int($v)) return $v > 0 ? $v : null;
        if (!is_string($v)) return null;

        $v = trim($v);
        if ($v === '') return null;
        if (!is_numeric($v)) return null;

        $i = (int) $v;
        return $i > 0 ? $i : null;
    }

    /**
     * Normaliza una lista/colección de IDs para whereIn:
     * - elimina null/''
     * - deja solo enteros > 0
     * - unique + values
     */
    private function normalizeIds($ids): Collection
    {
        return collect($ids)
            ->filter(function ($v) {
                if ($v === null) return false;
                if (is_int($v)) return $v > 0;
                if (is_string($v)) {
                    $t = trim($v);
                    return $t !== '' && is_numeric($t) && ((int)$t) > 0;
                }
                return is_numeric($v) && ((int)$v) > 0;
            })
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->values();
    }

    /* ===========================================================
     * RANGO FECHAS
     * =========================================================== */
    public function rangoFechas(array $filtros): array
    {
        $filtros = $this->normalizarFiltros($filtros);

        $desde = !empty($filtros['desde'])
            ? Carbon::parse($filtros['desde'])->startOfDay()
            : Carbon::now()->startOfYear();

        $hasta = !empty($filtros['hasta'])
            ? Carbon::parse($filtros['hasta'])->endOfDay()
            : Carbon::now()->endOfYear();

        return [$desde, $hasta];
    }

    /* ===========================================================
     * RESUMEN GLOBAL
     * =========================================================== */
    public function resumenGlobal(array $filtros, Carbon $desde, Carbon $hasta): array
    {
        $filtros = $this->normalizarFiltros($filtros);

        $ventasQuery = Venta::query()
            ->where('tipo_operacion', 'venta')
            ->whereBetween('fecha_venta', [$desde, $hasta]);

        if (!empty($filtros['proyecto_id'])) {
            $ventasQuery->where('id_proyecto', $filtros['proyecto_id']);
        }

        if (!empty($filtros['asesor_id'])) {
            $ventasQuery->where('id_empleado', $filtros['asesor_id']);
        }

        $ventasTotales = (float) $ventasQuery->sum('valor_total');
        $unidadesVendidas = (int) $ventasQuery->count();

        $estadoDisponibleId = EstadoInmueble::whereRaw('LOWER(nombre) = ?', ['disponible'])
            ->value('id_estado_inmueble');

        $inventarioAptos = $estadoDisponibleId
            ? Apartamento::where('id_estado_inmueble', $estadoDisponibleId)->count()
            : 0;

        $inventarioLocs = $estadoDisponibleId
            ? Local::where('id_estado_inmueble', $estadoDisponibleId)->count()
            : 0;

        return [
            'ventas_totales'        => $ventasTotales,
            'unidades_vendidas'     => $unidadesVendidas,
            'inventario_disponible' => $inventarioAptos + $inventarioLocs,
        ];
    }

    /* ===========================================================
     * VENTAS POR PROYECTO
     * =========================================================== */
    public function ventasPorProyecto(array $filtros, Carbon $desde, Carbon $hasta): array
    {
        $filtros = $this->normalizarFiltros($filtros);

        $q = Venta::query()
            ->select(
                'id_proyecto',
                DB::raw('SUM(valor_total) as total_valor'),
                DB::raw('COUNT(*) as unidades')
            )
            ->where('tipo_operacion', 'venta')
            ->whereBetween('fecha_venta', [$desde, $hasta])
            ->groupBy('id_proyecto');

        if (!empty($filtros['proyecto_id'])) {
            $q->where('id_proyecto', $filtros['proyecto_id']);
        }

        if (!empty($filtros['asesor_id'])) {
            $q->where('id_empleado', $filtros['asesor_id']);
        }

        $rows = $q->get();

        $ids = $this->normalizeIds($rows->pluck('id_proyecto'));
        if ($ids->isEmpty()) {
            // No ventas en el rango/filtro
            return [];
        }

        $proyectos = Proyecto::whereIn('id_proyecto', $ids->all())
            ->get()
            ->keyBy('id_proyecto');

        return $rows->map(function ($r) use ($proyectos) {
            $id = is_numeric($r->id_proyecto) ? (int) $r->id_proyecto : null;
            $p = ($id && isset($proyectos[$id])) ? $proyectos[$id] : null;

            return [
                'id_proyecto' => $id,
                'nombre'      => $p?->nombre ?? ('Proyecto ' . ($id ?? '—')),
                'total_valor' => (float) $r->total_valor,
                'unidades'    => (int) $r->unidades,
            ];
        })->values()->all();
    }

    /* ===========================================================
     * PROYECCIÓN VS REAL (usa Meta)
     * =========================================================== */
    public function proyeccionVsRealMensual(array $filtros, Carbon $desde, Carbon $hasta): array
    {
        $filtros = $this->normalizarFiltros($filtros);

        $ano = (int) $desde->year;
        $mes = (int) $desde->month;

        $metas = Meta::where('ano', $ano)
            ->where('mes', $mes)
            ->when(!empty($filtros['proyecto_id']), fn ($qq) => $qq->where('id_proyecto', $filtros['proyecto_id']))
            ->get()
            ->keyBy('id_proyecto');

        $ventasQuery = Venta::query()
            ->select(
                'id_proyecto',
                DB::raw('COUNT(*) as real_unidades'),
                DB::raw('SUM(valor_total) as real_valor')
            )
            ->where('tipo_operacion', 'venta')
            ->whereYear('fecha_venta', $ano)
            ->whereMonth('fecha_venta', $mes);

        if (!empty($filtros['proyecto_id'])) {
            $ventasQuery->where('id_proyecto', $filtros['proyecto_id']);
        }

        if (!empty($filtros['asesor_id'])) {
            $ventasQuery->where('id_empleado', $filtros['asesor_id']);
        }

        $ventas = $ventasQuery->groupBy('id_proyecto')->get();

        $ids = $this->normalizeIds(
            $ventas->pluck('id_proyecto')->merge($metas->keys())
        );

        if ($ids->isEmpty()) {
            return [];
        }

        $proyectos = Proyecto::whereIn('id_proyecto', $ids->all())
            ->get()
            ->keyBy('id_proyecto');

        $resultado = [];

        foreach ($ids as $id) {
            $p = $proyectos[$id] ?? null;
            if (!$p) continue;

            $meta  = $metas[$id] ?? null;
            $venta = $ventas->firstWhere('id_proyecto', $id);

            $resultado[] = [
                'id_proyecto'   => (int) $id,
                'nombre'        => $p->nombre,
                'meta_unidades' => (int) ($meta->meta_unidades ?? 0),
                'meta_valor'    => (float) ($meta->meta_valor ?? 0),
                'real_unidades' => (int) ($venta->real_unidades ?? 0),
                'real_valor'    => (float) ($venta->real_valor ?? 0),
            ];
        }

        return $resultado;
    }

    /* ===========================================================
     * VELOCIDAD DE VENTAS POR PROYECTO
     * =========================================================== */
    public function velocidadVentasPorProyecto(array $filtros, Carbon $desde, Carbon $hasta): array
    {
        $filtros = $this->normalizarFiltros($filtros);

        $ventasQ = Venta::query()
            ->where('tipo_operacion', 'venta')
            ->whereBetween('fecha_venta', [$desde, $hasta]);

        if (!empty($filtros['proyecto_id'])) {
            $ventasQ->where('id_proyecto', $filtros['proyecto_id']);
        }

        if (!empty($filtros['asesor_id'])) {
            $ventasQ->where('id_empleado', $filtros['asesor_id']);
        }

        $ventasGrouped = $ventasQ->get()->groupBy('id_proyecto');

        $ids = $this->normalizeIds($ventasGrouped->keys());
        if ($ids->isEmpty()) {
            return [];
        }

        $proyectos = Proyecto::whereIn('id_proyecto', $ids->all())
            ->get()
            ->keyBy('id_proyecto');

        $resultado = [];

        foreach ($ventasGrouped as $idProyecto => $ventasProyecto) {
            $id = is_numeric($idProyecto) ? (int) $idProyecto : null;
            if (!$id) continue;

            $proyecto = $proyectos[$id] ?? null;
            if (!$proyecto || !$proyecto->fecha_inicio) {
                continue;
            }

            $inicio = Carbon::parse($proyecto->fecha_inicio);

            $dias = $ventasProyecto->map(function ($v) use ($inicio) {
                if (!$v->fecha_venta) return null;
                return $inicio->diffInDays(Carbon::parse($v->fecha_venta));
            })->filter(fn ($x) => $x !== null);

            $promedio = $dias->count() ? round($dias->avg(), 1) : null;

            $resultado[] = [
                'id_proyecto'         => $id,
                'proyecto'            => $proyecto->nombre,
                'dias_promedio_venta' => $promedio,
            ];
        }

        return $resultado;
    }

    /* ===========================================================
     * SEPARACIONES / EFECTIVIDAD POR ASESOR
     * =========================================================== */
    public function separacionesYEfectividad(array $filtros, Carbon $desde, Carbon $hasta): array
    {
        $filtros = $this->normalizarFiltros($filtros);

        $q = Venta::query()
            ->where('tipo_operacion', 'separacion')
            ->whereBetween('fecha_venta', [$desde, $hasta]);

        if (!empty($filtros['proyecto_id'])) {
            $q->where('id_proyecto', $filtros['proyecto_id']);
        }

        if (!empty($filtros['asesor_id'])) {
            $q->where('id_empleado', $filtros['asesor_id']);
        }

        $rows = $q->get()->groupBy('id_empleado');

        $idsEmps = $this->normalizeIds($rows->keys());
        $empleados = $idsEmps->isEmpty()
            ? collect()
            : Empleado::whereIn('id_empleado', $idsEmps->all())->get()->keyBy('id_empleado');

        $hoy = Carbon::today();
        $resultado = [];

        foreach ($rows as $idEmpleado => $seps) {
            $id = is_numeric($idEmpleado) ? (int) $idEmpleado : null;
            if (!$id) continue;

            $emp = $empleados[$id] ?? null;

            $total      = $seps->count();
            $caducadas  = $seps->filter(function ($v) use ($hoy) {
                return $v->fecha_limite_separacion
                    ? Carbon::parse($v->fecha_limite_separacion)->lt($hoy)
                    : false;
            })->count();

            $ejecutadas = $total - $caducadas;

            $resultado[] = [
                'id_empleado'             => $id,
                'empleado'                => $emp ? ($emp->nombre . ' ' . $emp->apellido) : 'Sin nombre',
                'total_separaciones'      => $total,
                'separaciones_caducadas'  => $caducadas,
                'separaciones_ejecutadas' => $ejecutadas,
            ];
        }

        return $resultado;
    }

    /* ===========================================================
     * INVENTARIO POR PROYECTO (detallado)
     * =========================================================== */
    public function inventarioPorProyecto(array $filtros): array
    {
        $filtros = $this->normalizarFiltros($filtros);

        $proyectosQuery = Proyecto::query()->with([
            'torres.apartamentos.estadoInmueble',
            'torres.apartamentos.tipoApartamento',
            'torres.apartamentos.ventas' => function ($q) {
                $q->orderBy('fecha_venta', 'desc');
            },
            'torres.locales.estadoInmueble',
            'torres.locales.ventas' => function ($q) {
                $q->orderBy('fecha_venta', 'desc');
            },
        ]);

        if (!empty($filtros['proyecto_id'])) {
            $proyectosQuery->where('id_proyecto', $filtros['proyecto_id']);
        }

        $proyectos = $proyectosQuery->get();

        $estadoFiltroId = !empty($filtros['estado_inmueble'])
            ? (int) $filtros['estado_inmueble']
            : null;

        $result = [];

        foreach ($proyectos as $proyecto) {
            $items = [];

            foreach ($proyecto->torres as $torre) {
                foreach ($torre->apartamentos as $apto) {
                    if ($estadoFiltroId && (int)$apto->id_estado_inmueble !== (int)$estadoFiltroId) {
                        continue;
                    }

                    $ultimaVenta  = $apto->ventas->first();
                    $asesor       = $ultimaVenta?->empleado; // si existe relación
                    $estadoNombre = $apto->estadoInmueble?->nombre ?? '—';

                    $precioBase = (float) ($apto->tipoApartamento?->valor_estimado ?? 0);

                    $items[] = [
                        'tipo'            => 'Apartamento',
                        'etiqueta'        => 'Apto ' . $apto->numero,
                        'precio_base'     => $precioBase,
                        'precio_vigente'  => (float) ($apto->valor_final ?? $apto->valor_total ?? 0),
                        'estado'          => $estadoNombre,
                        'asesor'          => $asesor ? ($asesor->nombre . ' ' . $asesor->apellido) : null,
                        'fecha_operacion' => $ultimaVenta?->fecha_venta,
                    ];
                }

                foreach ($torre->locales as $loc) {
                    if ($estadoFiltroId && (int)$loc->id_estado_inmueble !== (int)$estadoFiltroId) {
                        continue;
                    }

                    $ultimaVenta  = $loc->ventas->first();
                    $asesor       = $ultimaVenta?->empleado; // si existe relación
                    $estadoNombre = $loc->estadoInmueble?->nombre ?? '—';

                    $items[] = [
                        'tipo'            => 'Local',
                        'etiqueta'        => 'Local ' . $loc->numero,
                        'precio_base'     => (float) ($loc->valor_total ?? 0),
                        'precio_vigente'  => (float) ($loc->valor_total ?? 0),
                        'estado'          => $estadoNombre,
                        'asesor'          => $asesor ? ($asesor->nombre . ' ' . $asesor->apellido) : null,
                        'fecha_operacion' => $ultimaVenta?->fecha_venta,
                    ];
                }
            }

            $result[] = [
                'id_proyecto' => $proyecto->id_proyecto,
                'nombre'      => $proyecto->nombre,
                'inmuebles'   => $items,
            ];
        }

        return $result;
    }

    /* ===========================================================
     * VENTAS / SEPARACIONES POR ASESOR Y PROYECTO
     * =========================================================== */
    public function ventasPorAsesorProyecto(array $filtros, Carbon $desde, Carbon $hasta): array
    {
        $filtros = $this->normalizarFiltros($filtros);

        $q = Venta::query()
            ->select(
                'id_proyecto',
                'id_empleado',
                DB::raw("SUM(CASE WHEN tipo_operacion = 'venta' THEN 1 ELSE 0 END) as ventas"),
                DB::raw("SUM(CASE WHEN tipo_operacion = 'separacion' THEN 1 ELSE 0 END) as separaciones"),
                DB::raw("SUM(CASE WHEN tipo_operacion = 'separacion' AND fecha_limite_separacion >= CURRENT_DATE THEN 1 ELSE 0 END) as separaciones_ejecutadas"),
                DB::raw("SUM(CASE WHEN tipo_operacion = 'separacion' AND fecha_limite_separacion <  CURRENT_DATE THEN 1 ELSE 0 END) as separaciones_caducadas")
            )
            ->whereBetween('fecha_venta', [$desde, $hasta])
            ->groupBy('id_proyecto', 'id_empleado');

        if (!empty($filtros['proyecto_id'])) {
            $q->where('id_proyecto', $filtros['proyecto_id']);
        }

        if (!empty($filtros['asesor_id'])) {
            $q->where('id_empleado', $filtros['asesor_id']);
        }

        $rows = $q->get();

        $idsProyectos = $this->normalizeIds($rows->pluck('id_proyecto'));
        $idsEmpleados = $this->normalizeIds($rows->pluck('id_empleado'));

        $proyectos = $idsProyectos->isEmpty()
            ? collect()
            : Proyecto::whereIn('id_proyecto', $idsProyectos->all())->get()->keyBy('id_proyecto');

        $empleados = $idsEmpleados->isEmpty()
            ? collect()
            : Empleado::whereIn('id_empleado', $idsEmpleados->all())->get()->keyBy('id_empleado');

        return $rows->map(function ($r) use ($proyectos, $empleados) {
            $idP = is_numeric($r->id_proyecto) ? (int) $r->id_proyecto : null;
            $idE = is_numeric($r->id_empleado) ? (int) $r->id_empleado : null;

            $p   = ($idP && isset($proyectos[$idP])) ? $proyectos[$idP] : null;
            $emp = ($idE && isset($empleados[$idE])) ? $empleados[$idE] : null;

            return [
                'id_proyecto'             => $idP,
                'proyecto'                => $p?->nombre ?? ('Proyecto ' . ($idP ?? '—')),
                'id_empleado'             => $idE,
                'empleado'                => $emp ? ($emp->nombre . ' ' . $emp->apellido) : 'Sin nombre',
                'ventas'                  => (int) $r->ventas,
                'separaciones'            => (int) $r->separaciones,
                'separaciones_ejecutadas' => (int) $r->separaciones_ejecutadas,
                'separaciones_caducadas'  => (int) $r->separaciones_caducadas,
            ];
        })->values()->all();
    }

    /* ============================================================
     * 1. ESTADO DEL INVENTARIO POR PROYECTO (doughnut)
     * =========================================================== */
    public function estadoInventario()
    {
        return Proyecto::with([
            'torres.apartamentos.estadoInmueble',
            'torres.locales.estadoInmueble',
        ])
            ->get()
            ->map(function ($p) {
                $estados = [
                    'Disponible'    => 0,
                    'Vendido'       => 0,
                    'Separado'      => 0,
                    'No Disponible' => 0,
                    'Congelado'     => 0,
                ];

                foreach ($p->torres as $torre) {
                    foreach ($torre->apartamentos as $a) {
                        $nombre = $a->estadoInmueble?->nombre;
                        if ($nombre && array_key_exists($nombre, $estados)) {
                            $estados[$nombre]++;
                        }
                    }

                    foreach ($torre->locales as $l) {
                        $nombre = $l->estadoInmueble?->nombre;
                        if ($nombre && array_key_exists($nombre, $estados)) {
                            $estados[$nombre]++;
                        }
                    }
                }

                return [
                    'proyecto' => $p->nombre,
                    'estados'  => $estados,
                ];
            });
    }

    /* ============================================================
     * 2. RANKING DE ASESORES POR VENTAS
     * =========================================================== */
    public function rankingAsesores()
    {
        return DB::table('ventas')
            ->join('empleados', 'empleados.id_empleado', '=', 'ventas.id_empleado')
            ->where('ventas.tipo_operacion', 'venta')
            ->select(
                'empleados.id_empleado',
                DB::raw("CONCAT(empleados.nombre, ' ', empleados.apellido) as asesor"),
                DB::raw("SUM(ventas.valor_total) as total_ventas")
            )
            ->groupBy('empleados.id_empleado', 'empleados.nombre', 'empleados.apellido')
            ->orderByDesc('total_ventas')
            ->get();
    }

    /* ============================================================
     * 3. ABSORCIÓN MENSUAL
     * =========================================================== */
    public function absorcionMensual()
    {
        return DB::table('ventas')
            ->join('proyectos', 'proyectos.id_proyecto', '=', 'ventas.id_proyecto')
            ->where('ventas.tipo_operacion', 'venta')
            ->select(
                'proyectos.nombre as proyecto',
                DB::raw("TO_CHAR(fecha_venta, 'YYYY-MM') as mes"),
                DB::raw("COUNT(*) as unidades")
            )
            ->groupBy('proyectos.nombre', DB::raw("TO_CHAR(fecha_venta, 'YYYY-MM')"))
            ->orderBy('mes')
            ->get();
    }

    /* ===========================================================
     * PLAN DE PAGOS DE CUOTA INICIAL (tabla horizontal)
     * =========================================================== */
    public function planPagosCI(array $filtros, Carbon $desde, Carbon $hasta): array
    {
        $filtros = $this->normalizarFiltros($filtros);

        $ventasQuery = Venta::with(['proyecto', 'apartamento', 'local', 'cliente'])
            ->where('tipo_operacion', 'venta')
            ->whereBetween('fecha_venta', [$desde, $hasta]);

        if (!empty($filtros['proyecto_id'])) {
            $ventasQuery->where('id_proyecto', $filtros['proyecto_id']);
        }

        if (!empty($filtros['asesor_id'])) {
            $ventasQuery->where('id_empleado', $filtros['asesor_id']);
        }

        $ventas = $ventasQuery->get();

        if ($ventas->isEmpty()) {
            return [
                'encabezados' => [],
                'filas'       => [],
                'totales'     => [],
            ];
        }

        $minMes = null;
        $maxMes = null;

        foreach ($ventas as $v) {
            if (!$v->fecha_venta) continue;

            $plazo = max(1, (int) ($v->plazo_cuota_inicial_meses ?? 1));

            $inicio = Carbon::parse($v->fecha_venta)->startOfMonth();
            $fin    = (clone $inicio)->addMonths($plazo);

            if ($minMes === null || $inicio->lt($minMes)) $minMes = $inicio->copy();
            if ($maxMes === null || $fin->gt($maxMes))    $maxMes = $fin->copy();
        }

        if (!$minMes || !$maxMes) {
            return [
                'encabezados' => [],
                'filas'       => [],
                'totales'     => [],
            ];
        }

        $encabezados = [];
        $cursor = $minMes->copy();
        while ($cursor <= $maxMes) {
            $encabezados[] = $cursor->format('Y-m');
            $cursor->addMonth();
        }

        $filas   = [];
        $totales = array_fill_keys($encabezados, 0);

        foreach ($ventas as $v) {
            $proyectoNombre = $v->proyecto?->nombre ?? ('Proyecto ' . ($v->id_proyecto ?? '—'));
            $clienteNombre  = $v->cliente?->nombre ?? '—';

            $inmueble = $v->apartamento?->numero
                ? 'Apto ' . $v->apartamento->numero
                : ($v->local?->numero
                    ? 'Local ' . $v->local->numero
                    : '—');

            $cuotaInicial   = (float) ($v->cuota_inicial ?? 0);
            $valorMinSep    = (float) ($v->proyecto?->valor_min_separacion ?? 0);
            $separacion     = (float) ($v->valor_separacion ?? $valorMinSep);
            $valorRestante  = max(0, (float) ($v->valor_total ?? 0) - $cuotaInicial);
            $saldoAmortizar = max(0, $cuotaInicial - $separacion);
            $plazo          = max(1, (int) ($v->plazo_cuota_inicial_meses ?? 1));

            $fechaBase = Carbon::parse($v->fecha_venta)->startOfMonth();

            $cuotaMensual = (int) floor($saldoAmortizar / $plazo);
            $residuo      = $saldoAmortizar - ($cuotaMensual * $plazo);

            $mesesRow = [];

            for ($i = 1; $i <= $plazo; $i++) {
                $mes = $fechaBase->format('Y-m');

                $valorCuota = $cuotaMensual;

                if ($i === $plazo) $valorCuota += $residuo;
                if ($i === 1)      $valorCuota += $separacion;

                $mesesRow[$mes] = ($mesesRow[$mes] ?? 0) + $valorCuota;
                if (isset($totales[$mes])) {
                    $totales[$mes] += $valorCuota;
                }

                $fechaBase->addMonth();
            }

            $mesRestante = $fechaBase->format('Y-m');
            if (isset($totales[$mesRestante])) {
                $mesesRow[$mesRestante] = ($mesesRow[$mesRestante] ?? 0) + $valorRestante;
                $totales[$mesRestante]  += $valorRestante;
            }

            $filas[] = [
                'proyecto' => $proyectoNombre,
                'inmueble' => $inmueble,
                'cliente'  => $clienteNombre,
                'meses'    => $mesesRow,
            ];
        }

        return [
            'encabezados' => $encabezados,
            'filas'       => $filas,
            'totales'     => $totales,
        ];
    }
}
