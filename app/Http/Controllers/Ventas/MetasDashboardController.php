<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\Meta;
use App\Models\Proyecto;
use App\Models\Venta;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MetasDashboardController extends Controller
{
    public function index(Request $request)
    {
        $empleado = $request->user()->load('cargo');

        $ano = (int)($request->query('ano', now()->year));
        $mesDesde = (int)($request->query('mes_desde', 1));
        $mesHasta = (int)($request->query('mes_hasta', 12));
        $fProyecto = $request->query('id_proyecto', null);

        $proyectos = Proyecto::select('id_proyecto', 'nombre')->orderBy('nombre')->get();

        $metasQuery = Meta::with(['proyecto', 'empleado'])
            ->where('ano', $ano)
            ->whereBetween('mes', [$mesDesde, $mesHasta])
            ->where(function ($q) use ($empleado) {
                $q->whereNull('id_empleado')
                    ->orWhere('id_empleado', $empleado->id_empleado);
            });

        if ($fProyecto) {
            $metasQuery->where('id_proyecto', $fProyecto);
        }

        $metas = $metasQuery->get();

        $ventasQuery = Venta::selectRaw('
                id_proyecto,
                id_empleado,
                EXTRACT(MONTH FROM fecha_venta)::int as mes,
                EXTRACT(YEAR FROM fecha_venta)::int as ano,
                COUNT(*) as unidades,
                SUM(valor_total) as valor_total
            ')
            ->whereRaw('EXTRACT(YEAR FROM fecha_venta) = ?', [$ano])
            ->whereRaw('EXTRACT(MONTH FROM fecha_venta) BETWEEN ? AND ?', [$mesDesde, $mesHasta])
            ->where('tipo_operacion', 'venta');

        if ($fProyecto) {
            $ventasQuery->where('id_proyecto', $fProyecto);
        }

        $ventasAgg = $ventasQuery
            ->groupBy('id_proyecto', 'id_empleado', 'mes', 'ano')
            ->get();

        $resultadosIndex = [];
        foreach ($ventasAgg as $v) {
            $key = $v->id_proyecto . '|' . $v->mes . '|' . $v->ano . '|' . ($v->id_empleado ?? 0);
            $resultadosIndex[$key] = [
                'unidades' => (int)$v->unidades,
                'valor_total' => (float)$v->valor_total,
            ];
        }

        $metasEnriquecidas = $metas->map(function (Meta $m) use ($resultadosIndex) {
            $keyBase = $m->id_proyecto . '|' . $m->mes . '|' . $m->ano;

            if (!empty($m->id_empleado)) {
                // Meta por asesor (solo ese asesor)
                $key = $keyBase . '|' . $m->id_empleado;
                $res = $resultadosIndex[$key] ?? ['unidades' => 0, 'valor_total' => 0];
            } else {
                // Meta de equipo → sumar todas las ventas del proyecto
                $res = ['unidades' => 0, 'valor_total' => 0];
                foreach ($resultadosIndex as $k => $v) {
                    [$pId, $mes, $ano, $empId] = explode('|', $k);
                    if ($pId == $m->id_proyecto && $mes == $m->mes && $ano == $m->ano) {
                        $res['unidades'] += $v['unidades'];
                        $res['valor_total'] += $v['valor_total'];
                    }
                }
            }

            $metaValor = (float)($m->meta_valor ?? 0);
            $metaUnidades = (int)($m->meta_unidades ?? 0);
            $realValor = (float)$res['valor_total'];
            $realUnidades = (int)$res['unidades'];

            $cumplValor = $metaValor > 0 ? $realValor / $metaValor : null;
            $cumplUnid = $metaUnidades > 0 ? $realUnidades / $metaUnidades : null;

            return [
                'id_meta' => $m->id_meta,
                'tipo' => $m->tipo,
                'mes' => $m->mes,
                'ano' => $m->ano,
                'meta_valor' => $metaValor,
                'meta_unidades' => $metaUnidades,
                'id_proyecto' => $m->id_proyecto,
                'id_empleado' => $m->id_empleado,
                'proyecto' => $m->proyecto ? $m->proyecto->nombre : null,
                'empleado' => $m->empleado ? ($m->empleado->nombre . ' ' . $m->empleado->apellido) : null,
                'real_valor' => $realValor,
                'real_unidades' => $realUnidades,
                'cumplimiento_valor' => $cumplValor,
                'cumplimiento_unidades' => $cumplUnid,
            ];
        });

        $alertas = $metasEnriquecidas->filter(function ($m) {
            $critValor = $m['meta_valor'] > 0 && $m['cumplimiento_valor'] !== null && $m['cumplimiento_valor'] < 0.8;
            $critUnid = $m['meta_unidades'] > 0 && $m['cumplimiento_unidades'] !== null && $m['cumplimiento_unidades'] < 0.8;
            return $critValor || $critUnid;
        })->values();

        $resumenProyecto = $metasEnriquecidas
            ->groupBy('id_proyecto')
            ->map(function ($items, $idProj) {
                $nombre = $items->first()['proyecto'] ?? 'General';
                $metaValor = $items->sum('meta_valor');
                $realValor = $items->sum('real_valor');
                $metaUni = $items->sum('meta_unidades');
                $realUni = $items->sum('real_unidades');

                return [
                    'id_proyecto' => (int)$idProj,
                    'proyecto' => $nombre,
                    'meta_valor' => $metaValor,
                    'real_valor' => $realValor,
                    'meta_unidades' => $metaUni,
                    'real_unidades' => $realUni,
                    'cumplimiento_valor' => $metaValor > 0 ? $realValor / $metaValor : null,
                    'cumplimiento_unidades' => $metaUni > 0 ? $realUni / $metaUni : null,
                ];
            })
            ->values()
            ->sortBy('proyecto')
            ->values();

        $resumenAsesor = $metasEnriquecidas
            ->groupBy('id_empleado')
            ->map(function ($items, $idEmp) {
                $nombre = $items->first()['empleado'] ?? 'General';
                $metaValor = $items->sum('meta_valor');
                $realValor = $items->sum('real_valor');
                $metaUni = $items->sum('meta_unidades');
                $realUni = $items->sum('real_unidades');

                return [
                    'id_empleado' => (int)$idEmp,
                    'empleado' => $nombre,
                    'meta_valor' => $metaValor,
                    'real_valor' => $realValor,
                    'meta_unidades' => $metaUni,
                    'real_unidades' => $realUni,
                    'cumplimiento_valor' => $metaValor > 0 ? $realValor / $metaValor : null,
                    'cumplimiento_unidades' => $metaUni > 0 ? $realUni / $metaUni : null,
                ];
            })
            ->values()
            ->sortBy('empleado')
            ->values();

        return Inertia::render('Ventas/Metas/Index', [
            'metas' => $metasEnriquecidas,
            'alertas' => $alertas,
            'resumenProyecto' => $resumenProyecto,
            'resumenAsesor' => $resumenAsesor,
            'proyectos' => $proyectos,
            'filtros' => [
                'ano' => $ano,
                'mes_desde' => $mesDesde,
                'mes_hasta' => $mesHasta,
                'id_proyecto' => $fProyecto,
            ],
            'empleado' => $empleado,
        ]);
    }
}
