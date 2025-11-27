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
use Illuminate\Support\Facades\DB;

class GerenciaEstadisticasService
{
    /**
     * Punto único para obtener todos los bloques de datos del dashboard
     */
    public function obtenerDashboard(array $filtros): array
    {
        [$desde, $hasta] = $this->rangoFechas($filtros);

        return [
            'resumenGlobal'          => $this->resumenGlobal($filtros, $desde, $hasta),
            'ventasPorProyecto'      => $this->ventasPorProyecto($filtros, $desde, $hasta),
            'proyeccionVsReal'       => $this->proyeccionVsRealMensual($filtros, $desde, $hasta),
            'velocidadVentas'        => $this->velocidadVentasPorProyecto($filtros, $desde, $hasta),
            'separacionesEfectiv'    => $this->separacionesYEfectividad($filtros, $desde, $hasta),
            'inventarioProyectos'    => $this->inventarioPorProyecto($filtros),
            'ventasAsesoresProyecto' => $this->ventasPorAsesorProyecto($filtros, $desde, $hasta),

            // Nuevos bloques para las 3 gráficas del resumen
            'estadoInventario'       => $this->estadoInventario(),
            'rankingAsesores'        => $this->rankingAsesores(),
            'absorcionMensual'       => $this->absorcionMensual(),
        ];
    }

    /**
     * Si no vienen fechas, tomamos año actual.
     */
    public function rangoFechas(array $filtros): array
    {
        $desde = !empty($filtros['desde'])
            ? Carbon::parse($filtros['desde'])->startOfDay()
            : Carbon::now()->startOfYear();

        $hasta = !empty($filtros['hasta'])
            ? Carbon::parse($filtros['hasta'])->endOfDay()
            : Carbon::now()->endOfYear();

        return [$desde, $hasta];
    }

    /* ===========================================================
     *  RESUMEN GLOBAL
     * =========================================================== */
    public function resumenGlobal(array $filtros, Carbon $desde, Carbon $hasta): array
    {
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

        // Inventario disponible actual
        $estadoDisponibleId = EstadoInmueble::whereRaw('LOWER(nombre) = ?', ['disponible'])
            ->value('id_estado_inmueble');

        $inventarioAptos = Apartamento::where('id_estado_inmueble', $estadoDisponibleId)->count();
        $inventarioLocs  = Local::where('id_estado_inmueble', $estadoDisponibleId)->count();

        return [
            'ventas_totales'        => $ventasTotales,
            'unidades_vendidas'     => $unidadesVendidas,
            'inventario_disponible' => $inventarioAptos + $inventarioLocs,
        ];
    }

    /* ===========================================================
     *  VENTAS POR PROYECTO
     * =========================================================== */
    public function ventasPorProyecto(array $filtros, Carbon $desde, Carbon $hasta)
    {
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

        $proyectos = Proyecto::whereIn('id_proyecto', $rows->pluck('id_proyecto'))
            ->get()
            ->keyBy('id_proyecto');

        return $rows->map(function ($r) use ($proyectos) {
            $p = $proyectos[$r->id_proyecto] ?? null;
            return [
                'id_proyecto' => $r->id_proyecto,
                'nombre'      => $p?->nombre ?? 'Proyecto ' . $r->id_proyecto,
                'total_valor' => (float) $r->total_valor,
                'unidades'    => (int) $r->unidades,
            ];
        })->values()->all();
    }

    /* ===========================================================
     *  PROYECCIÓN VS REAL (usa Meta)
     * =========================================================== */
    public function proyeccionVsRealMensual(array $filtros, Carbon $desde, Carbon $hasta)
    {
        $ano = (int) $desde->year;
        $mes = (int) $desde->month;

        $metas = Meta::where('ano', $ano)
            ->where('mes', $mes)
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

        $proyectos = Proyecto::whereIn('id_proyecto', $ventas->pluck('id_proyecto')->merge($metas->keys()))
            ->get()
            ->keyBy('id_proyecto');

        $resultado = [];

        foreach ($proyectos as $id => $p) {
            $meta  = $metas[$id] ?? null;
            $venta = $ventas->firstWhere('id_proyecto', $id);

            $resultado[] = [
                'id_proyecto'   => $id,
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
     *  VELOCIDAD DE VENTAS POR PROYECTO
     * =========================================================== */
    public function velocidadVentasPorProyecto(array $filtros, Carbon $desde, Carbon $hasta)
    {
        $ventas = Venta::query()
            ->where('tipo_operacion', 'venta')
            ->whereBetween('fecha_venta', [$desde, $hasta]);

        if (!empty($filtros['proyecto_id'])) {
            $ventas->where('id_proyecto', $filtros['proyecto_id']);
        }

        if (!empty($filtros['asesor_id'])) {
            $ventas->where('id_empleado', $filtros['asesor_id']);
        }

        $ventas = $ventas->get()->groupBy('id_proyecto');

        $proyectos = Proyecto::whereIn('id_proyecto', $ventas->keys())
            ->get()
            ->keyBy('id_proyecto');

        $resultado = [];

        foreach ($ventas as $idProyecto => $ventasProyecto) {
            $proyecto = $proyectos[$idProyecto] ?? null;
            if (!$proyecto || !$proyecto->fecha_inicio) {
                continue;
            }

            $inicio = Carbon::parse($proyecto->fecha_inicio);
            $dias = $ventasProyecto->map(function ($v) use ($inicio) {
                return $inicio->diffInDays(Carbon::parse($v->fecha_venta));
            });

            $promedio = $dias->count() ? round($dias->avg(), 1) : null;

            $resultado[] = [
                'id_proyecto'         => $idProyecto,
                'proyecto'            => $proyecto->nombre,
                'dias_promedio_venta' => $promedio,
            ];
        }

        return $resultado;
    }

    /* ===========================================================
     *  SEPARACIONES / EFECTIVIDAD POR ASESOR
     * =========================================================== */
    public function separacionesYEfectividad(array $filtros, Carbon $desde, Carbon $hasta)
    {
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

        $empleados = Empleado::whereIn('id_empleado', $rows->keys())
            ->get()
            ->keyBy('id_empleado');

        $hoy = Carbon::today();

        $resultado = [];

        foreach ($rows as $idEmpleado => $seps) {
            $emp = $empleados[$idEmpleado] ?? null;

            $total      = $seps->count();
            $caducadas  = $seps->where('fecha_limite_separacion', '<', $hoy)->count();
            $ejecutadas = $total - $caducadas;

            $resultado[] = [
                'id_empleado'             => $idEmpleado,
                'empleado'                => $emp ? ($emp->nombre . ' ' . $emp->apellido) : 'Sin nombre',
                'total_separaciones'      => $total,
                'separaciones_caducadas'  => $caducadas,
                'separaciones_ejecutadas' => $ejecutadas,
            ];
        }

        return $resultado;
    }

    /* ===========================================================
     *  INVENTARIO POR PROYECTO (detallado)
     * =========================================================== */
    public function inventarioPorProyecto(array $filtros)
    {
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
                    if ($estadoFiltroId && $apto->id_estado_inmueble != $estadoFiltroId) {
                        continue;
                    }

                    $ultimaVenta  = $apto->ventas->first();
                    $asesor       = $ultimaVenta?->empleado;
                    $estadoNombre = $apto->estadoInmueble?->nombre ?? '—';

                    // NUEVO: obtener precio base desde tipo de apartamento
                    $precioBase = $apto->tipoApartamento->valor_estimado
                        ?? 0;

                    $items[] = [
                        'tipo'            => 'Apartamento',
                        'etiqueta'        => 'Apto ' . $apto->numero,
                        'precio_base'     => (float) $precioBase,
                        'precio_vigente'  => (float) ($apto->valor_final ?? $apto->valor_total ?? 0),
                        'estado'          => $estadoNombre,
                        'asesor'          => $asesor ? ($asesor->nombre . ' ' . $asesor->apellido) : null,
                        'fecha_operacion' => $ultimaVenta?->fecha_venta,
                    ];
                }

                foreach ($torre->locales as $loc) {
                    if ($estadoFiltroId && $loc->id_estado_inmueble != $estadoFiltroId) {
                        continue;
                    }

                    $ultimaVenta  = $loc->ventas->first();
                    $asesor       = $ultimaVenta?->empleado;
                    $estadoNombre = $loc->estadoInmueble?->nombre ?? '—';

                    $items[] = [
                        'tipo'            => 'Local',
                        'etiqueta'        => 'Local ' . $loc->numero,
                        'precio_base'     => (float) $loc->valor_total,
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
     *  VENTAS / SEPARACIONES POR ASESOR Y PROYECTO
     * =========================================================== */
    public function ventasPorAsesorProyecto(array $filtros, Carbon $desde, Carbon $hasta)
    {
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

        $proyectos = Proyecto::whereIn('id_proyecto', $rows->pluck('id_proyecto'))
            ->get()
            ->keyBy('id_proyecto');

        $empleados = Empleado::whereIn('id_empleado', $rows->pluck('id_empleado'))
            ->get()
            ->keyBy('id_empleado');

        return $rows->map(function ($r) use ($proyectos, $empleados) {
            $p   = $proyectos[$r->id_proyecto] ?? null;
            $emp = $empleados[$r->id_empleado] ?? null;

            return [
                'id_proyecto'             => $r->id_proyecto,
                'proyecto'                => $p?->nombre ?? 'Proyecto ' . $r->id_proyecto,
                'id_empleado'             => $r->id_empleado,
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
            'torres.locales.estadoInmueble'
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
                        $nombre = $a->estadoInmueble->nombre ?? null;
                        if ($nombre && isset($estados[$nombre])) {
                            $estados[$nombre]++;
                        }
                    }

                    foreach ($torre->locales as $l) {
                        $nombre = $l->estadoInmueble->nombre ?? null;
                        if ($nombre && isset($estados[$nombre])) {
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

        /* ============================================================
       1. DEFINIR RANGO GLOBAL DE MESES
       ============================================================ */
        $minMes = null;
        $maxMes = null;

        foreach ($ventas as $v) {
            if (!$v->fecha_venta) continue;

            $plazo = max(1, (int)$v->plazo_cuota_inicial_meses);

            $inicio = Carbon::parse($v->fecha_venta)->startOfMonth();
            $fin    = (clone $inicio)->addMonths($plazo);

            if ($minMes === null || $inicio->lt($minMes)) {
                $minMes = $inicio->copy();
            }
            if ($maxMes === null || $fin->gt($maxMes)) {
                $maxMes = $fin->copy();
            }
        }

        if (!$minMes || !$maxMes) {
            return [
                'encabezados' => [],
                'filas'       => [],
                'totales'     => [],
            ];
        }

        /* ============================================================
       2. ENCABEZADOS (MESES DINÁMICOS)
       ============================================================ */
        $encabezados = [];
        $cursor = $minMes->copy();
        while ($cursor <= $maxMes) {
            $encabezados[] = $cursor->format('Y-m');
            $cursor->addMonth();
        }

        $filas   = [];
        $totales = array_fill_keys($encabezados, 0);

        /* ============================================================
       3. RECORRER VENTAS Y ARMAR FILAS
       ============================================================ */
        foreach ($ventas as $v) {

            $proyecto = $v->proyecto->nombre ?? ('Proyecto ' . $v->id_proyecto);
            $cliente  = $v->cliente->nombre ?? '—';

            $inmueble = $v->apartamento?->numero
                ? 'Apto ' . $v->apartamento->numero
                : ($v->local?->numero
                    ? 'Local ' . $v->local->numero
                    : '—');

            // ===== Datos de la venta =====
            $cuotaInicial   = (float)($v->cuota_inicial ?? 0);
            $separacion     = (float)($v->valor_separacion ?? $v->proyecto->valor_min_separacion ?? 0);
            $valorRestante  = max(0, (float)$v->valor_total - $cuotaInicial);
            $saldoAmortizar = max(0, $cuotaInicial - $separacion);
            $plazo          = max(1, (int)$v->plazo_cuota_inicial_meses);

            // Fechas
            $fechaBase = Carbon::parse($v->fecha_venta)->startOfMonth();

            // Cuotas CI
            $cuotaMensual = (int) floor($saldoAmortizar / $plazo);
            $residuo      = $saldoAmortizar - ($cuotaMensual * $plazo);

            // Meses por venta
            $mesesRow = [];

            /* ============================================================
           3.1. MESES DE LA CUOTA INICIAL (Plazo CI)
           ============================================================ */
            for ($i = 1; $i <= $plazo; $i++) {

                $mes = $fechaBase->format('Y-m');

                // Cuota base
                $valorCuota = $cuotaMensual;

                // Última cuota → sumamos residuo
                if ($i === $plazo) {
                    $valorCuota += $residuo;
                }

                // Mes 1 → sumamos separación
                if ($i === 1) {
                    $valorCuota += $separacion;
                }

                // Guardar
                $mesesRow[$mes] = ($mesesRow[$mes] ?? 0) + $valorCuota;
                $totales[$mes]  += $valorCuota;

                // Avanzar al siguiente mes
                $fechaBase->addMonth();
            }

            /* ============================================================
           3.2. MES SIGUIENTE AL PLAZO → VALOR RESTANTE
           ============================================================ */
            $mesRestante = $fechaBase->format('Y-m');

            if (isset($totales[$mesRestante])) {
                $mesesRow[$mesRestante] = ($mesesRow[$mesRestante] ?? 0) + $valorRestante;
                $totales[$mesRestante]  += $valorRestante;
            }

            /* ============================================================
           3.3. AGREGAR FILA
           ============================================================ */
            $filas[] = [
                'proyecto' => $proyecto,
                'inmueble' => $inmueble,
                'cliente'  => $cliente,
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
