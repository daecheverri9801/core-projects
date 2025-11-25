<?php

namespace App\Services;

use App\Models\Proyecto;
use App\Models\Venta;
use App\Models\Apartamento;
use App\Models\Local;
use App\Models\EstadoInmueble;
use App\Models\ProyectoMetaComercial;
use App\Models\Empleado;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GerenciaEstadisticasService
{
    /* ============================================================
     *  RESUMEN GLOBAL
     * ============================================================ */
    public function resumenGlobal()
    {
        $ventasTotales = Venta::where('tipo_operacion', 'venta')->sum('valor_total');
        $unidadesVendidas = Venta::where('tipo_operacion', 'venta')->count();

        $idEstadoDisponible = EstadoInmueble::where('nombre', 'Disponible')
            ->value('id_estado_inmueble');

        $inventarioDisponible = 0;
        if ($idEstadoDisponible) {
            $inventarioDisponible =
                Apartamento::where('id_estado_inmueble', $idEstadoDisponible)->count() +
                Local::where('id_estado_inmueble', $idEstadoDisponible)->count();
        }

        return [
            'ventas_totales'        => (float) $ventasTotales,
            'unidades_vendidas'     => (int) $unidadesVendidas,
            'inventario_disponible' => (int) $inventarioDisponible,
        ];
    }

    /* ============================================================
     *  VENTAS POR PROYECTO
     * ============================================================ */
    public function ventasPorProyecto()
    {
        $ventas = Venta::select(
            'id_proyecto',
            DB::raw('COUNT(*) as unidades'),
            DB::raw('SUM(valor_total) as total_valor')
        )
            ->where('tipo_operacion', 'venta')
            ->groupBy('id_proyecto')
            ->get()
            ->keyBy('id_proyecto');

        $proyectos = Proyecto::select('id_proyecto', 'nombre')->get();

        return $proyectos->map(function ($p) use ($ventas) {
            $v = $ventas->get($p->id_proyecto);

            return [
                'id_proyecto' => $p->id_proyecto,
                'nombre'      => $p->nombre,
                'unidades'    => (int) ($v->unidades ?? 0),
                'total_valor' => (float) ($v->total_valor ?? 0),
            ];
        })->values();
    }

    /* ============================================================
     *  PROYECCIÓN VS REAL (MES ACTUAL)
     * ============================================================ */
    public function proyeccionVsRealMensual()
    {
        $hoy = Carbon::today();
        $mesActual = $hoy->format('Y-m');

        // Ventas reales del mes
        $ventasMes = Venta::where('tipo_operacion', 'venta')
            ->whereBetween('fecha_venta', [
                $hoy->copy()->startOfMonth(),
                $hoy->copy()->endOfMonth(),
            ])
            ->select(
                'id_proyecto',
                DB::raw('COUNT(*) as unidades'),
                DB::raw('SUM(valor_total) as total_valor')
            )
            ->groupBy('id_proyecto')
            ->get()
            ->keyBy('id_proyecto');

        // Metas comerciales del mes
        $metas = ProyectoMetaComercial::where('mes_anio', $mesActual)
            ->get()
            ->keyBy('id_proyecto');

        $proyectos = Proyecto::select('id_proyecto', 'nombre')->get();

        return $proyectos->map(function ($p) use ($ventasMes, $metas) {
            $venta = $ventasMes->get($p->id_proyecto);
            $meta  = $metas->get($p->id_proyecto);

            return [
                'id_proyecto'   => $p->id_proyecto,
                'nombre'        => $p->nombre,
                'meta_unidades' => (int) ($meta->meta_unidades ?? 0),
                'real_unidades' => (int) ($venta->unidades ?? 0),
                'meta_valor'    => (float) ($meta->meta_valor ?? 0),
                'real_valor'    => (float) ($venta->total_valor ?? 0),
            ];
        })->values();
    }

    /* ============================================================
     *  VELOCIDAD DE VENTAS POR PROYECTO
     * ============================================================ */
    public function velocidadVentasPorProyecto()
    {
        $ventas = Venta::where('tipo_operacion', 'venta')
            ->with('proyecto')
            ->get();

        $agrupado = [];

        foreach ($ventas as $v) {
            if (!$v->proyecto || !$v->proyecto->fecha_inicio) {
                continue;
            }

            $inicio  = Carbon::parse($v->proyecto->fecha_inicio);
            $ventaAt = Carbon::parse($v->fecha_venta);

            $dias = $inicio->diffInDays($ventaAt);
            $pid  = $v->id_proyecto;

            if (!isset($agrupado[$pid])) {
                $agrupado[$pid] = [
                    'proyecto'   => $v->proyecto->nombre,
                    'total_dias' => 0,
                    'ventas'     => 0,
                ];
            }

            $agrupado[$pid]['total_dias'] += $dias;
            $agrupado[$pid]['ventas']++;
        }

        return collect($agrupado)->map(function ($item) {
            $prom = $item['ventas'] > 0 ? $item['total_dias'] / $item['ventas'] : 0;

            return [
                'proyecto'            => $item['proyecto'],
                'dias_promedio_venta' => round($prom, 1),
            ];
        })->values();
    }

    /* ============================================================
     *  SEPARACIONES / EFECTIVIDAD POR ASESOR (GLOBAL)
     *  (Cambia ID por nombre + apellido)
     * ============================================================ */
    public function separacionesYEfectividad()
    {
        $rows = Venta::where('tipo_operacion', 'separacion')
            ->select(
                'id_empleado',
                DB::raw('COUNT(*) as total_separaciones'),
                DB::raw("SUM(CASE WHEN estado_operacion = 'ejecutada' THEN 1 ELSE 0 END) as separaciones_ejecutadas"),
                DB::raw("SUM(CASE WHEN estado_operacion = 'caducada' THEN 1 ELSE 0 END) as separaciones_caducadas")
            )
            ->groupBy('id_empleado')
            ->get();

        $empleados = Empleado::whereIn('id_empleado', $rows->pluck('id_empleado')->filter())
            ->get()
            ->keyBy('id_empleado');

        return $rows->map(function ($r) use ($empleados) {
            $emp = $empleados->get($r->id_empleado);

            return [
                'id_empleado'            => $r->id_empleado,
                'empleado'               => $emp ? ($emp->nombre . ' ' . $emp->apellido) : 'Sin asignar',
                'total_separaciones'     => (int) $r->total_separaciones,
                'separaciones_ejecutadas' => (int) $r->separaciones_ejecutadas,
                'separaciones_caducadas' => (int) $r->separaciones_caducadas,
            ];
        });
    }

    /* ============================================================
     *  INVENTARIO DETALLADO POR PROYECTO
     *  (Proyectos → listado de inmuebles con precio base / vigente / estado)
     * ============================================================ */
    public function inventarioProyectosDetalle()
    {
        $idEstadoDisponible = EstadoInmueble::where('nombre', 'Disponible')
            ->value('id_estado_inmueble');

        $apartamentos = Apartamento::with(['torre.proyecto', 'estadoInmueble', 'tipoApartamento'])
            ->get();

        $locales = Local::with(['torre.proyecto', 'estadoInmueble'])
            ->get();

        $proyectos = [];

        // Apartamentos
        foreach ($apartamentos as $a) {
            if (!$a->torre || !$a->torre->proyecto) continue;

            $p = $a->torre->proyecto;
            $pid = $p->id_proyecto;

            if (!isset($proyectos[$pid])) {
                $proyectos[$pid] = [
                    'id_proyecto' => $pid,
                    'nombre'      => $p->nombre,
                    'inmuebles'   => [],
                ];
            }

            $precioBase = $a->tipoApartamento->valor_estimado ?? 0;
            $precioVig  = $a->valor_final ?? $a->valor_total ?? 0;

            $proyectos[$pid]['inmuebles'][] = [
                'tipo'           => 'Apartamento',
                'etiqueta'       => 'Torre ' . ($a->torre->nombre ?? ('ID ' . $a->torre->id_torre)) . ' - Apto ' . $a->numero,
                'precio_base'    => (float) ($precioBase ?: 0),
                'precio_vigente' => (float) ($precioVig ?: 0),
                'estado'         => $a->estadoInmueble->nombre ?? 'Sin estado',
                'disponible'     => ($idEstadoDisponible && $a->id_estado_inmueble == $idEstadoDisponible) ? true : false,
            ];
        }

        // Locales
        foreach ($locales as $l) {
            if (!$l->torre || !$l->torre->proyecto) continue;

            $p = $l->torre->proyecto;
            $pid = $p->id_proyecto;

            if (!isset($proyectos[$pid])) {
                $proyectos[$pid] = [
                    'id_proyecto' => $pid,
                    'nombre'      => $p->nombre,
                    'inmuebles'   => [],
                ];
            }

            $precioBase = $l->valor_m2 * ($l->area_total_local ?? 0);
            $precioVig  = $l->valor_final ?? $l->valor_total ?? 0;

            $proyectos[$pid]['inmuebles'][] = [
                'tipo'           => 'Local',
                'etiqueta'       => 'Torre ' . ($l->torre->nombre ?? ('ID ' . $l->torre->id_torre)) . ' - Local ' . $l->numero,
                'precio_base'    => (float) $precioBase,
                'precio_vigente' => (float) $precioVig,
                'estado'         => $l->estadoInmueble->nombre ?? 'Sin estado',
                'disponible'     => ($idEstadoDisponible && $l->id_estado_inmueble == $idEstadoDisponible) ? true : false,
            ];
        }

        return collect($proyectos)->values();
    }

    /* ============================================================
     *  VENTAS / SEPARACIONES POR ASESOR Y PROYECTO
     * ============================================================ */
    public function ventasSeparacionesPorProyectoAsesor()
    {
        $rows = Venta::select(
            'id_proyecto',
            'id_empleado',
            DB::raw("SUM(CASE WHEN tipo_operacion = 'venta' THEN 1 ELSE 0 END) as ventas"),
            DB::raw("SUM(CASE WHEN tipo_operacion = 'separacion' THEN 1 ELSE 0 END) as separaciones"),
            DB::raw("SUM(CASE WHEN tipo_operacion = 'separacion' AND estado_operacion = 'ejecutada' THEN 1 ELSE 0 END) as separaciones_ejecutadas"),
            DB::raw("SUM(CASE WHEN tipo_operacion = 'separacion' AND estado_operacion = 'caducada' THEN 1 ELSE 0 END) as separaciones_caducadas")
        )
            ->groupBy('id_proyecto', 'id_empleado')
            ->get();

        $proyectos = Proyecto::whereIn('id_proyecto', $rows->pluck('id_proyecto')->filter())
            ->get()
            ->keyBy('id_proyecto');

        $empleados = Empleado::whereIn('id_empleado', $rows->pluck('id_empleado')->filter())
            ->get()
            ->keyBy('id_empleado');

        return $rows->map(function ($r) use ($proyectos, $empleados) {
            $proy = $proyectos->get($r->id_proyecto);
            $emp  = $empleados->get($r->id_empleado);

            return [
                'id_proyecto'            => $r->id_proyecto,
                'proyecto'               => $proy?->nombre ?? 'Sin proyecto',
                'id_empleado'            => $r->id_empleado,
                'empleado'               => $emp ? ($emp->nombre . ' ' . $emp->apellido) : 'Sin asesor',
                'ventas'                 => (int) $r->ventas,
                'separaciones'           => (int) $r->separaciones,
                'separaciones_ejecutadas' => (int) $r->separaciones_ejecutadas,
                'separaciones_caducadas' => (int) $r->separaciones_caducadas,
            ];
        })->sortBy(['proyecto', 'empleado'])->values();
    }
}
