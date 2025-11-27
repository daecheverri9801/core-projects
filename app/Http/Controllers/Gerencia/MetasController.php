<?php

namespace App\Http\Controllers\Gerencia;

use App\Http\Controllers\Controller;
use App\Models\Meta;
use App\Models\Proyecto;
use App\Models\Empleado;
use App\Models\Venta;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;


class MetasController extends Controller
{
    public function index(Request $request)
    {
        $ano = (int)($request->query('ano', now()->year));
        $mesDesde = (int)($request->query('mes_desde', 1));
        $mesHasta = (int)($request->query('mes_hasta', 12));
        $fProyecto = $request->query('id_proyecto', null);
        $fEmpleado = $request->query('id_empleado', null);

        $proyectos = Proyecto::select('id_proyecto', 'nombre')->orderBy('nombre')->get();
        $empleados = Empleado::select('id_empleado', 'nombre', 'apellido')->orderBy('nombre')->get();

        $metasQuery = Meta::with(['proyecto', 'empleado'])
            ->where('ano', $ano)
            ->whereBetween('mes', [$mesDesde, $mesHasta]);

        if ($fProyecto) {
            $metasQuery->where('id_proyecto', $fProyecto);
        }
        if ($fEmpleado) {
            $metasQuery->where('id_empleado', $fEmpleado);
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
        if ($fEmpleado) {
            $ventasQuery->where('id_empleado', $fEmpleado);
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
            // Claves: Proyecto | Mes | Año | Asesor opcional
            $keyBase = $m->id_proyecto . '|' . $m->mes . '|' . $m->ano;

            // Si la meta es por asesor → buscar ventas del asesor
            if (!empty($m->id_empleado)) {
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

        return Inertia::render('Gerencia/Metas/Index', [
            'metas' => $metasEnriquecidas,
            'alertas' => $alertas,
            'resumenProyecto' => $resumenProyecto,
            'resumenAsesor' => $resumenAsesor,
            'proyectos' => $proyectos,
            'empleados' => $empleados,
            'filtros' => [
                'ano' => $ano,
                'mes_desde' => $mesDesde,
                'mes_hasta' => $mesHasta,
                'id_proyecto' => $fProyecto,
                'id_empleado' => $fEmpleado,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo' => 'required',
            'mes' => 'required|integer|min:1|max:12',
            'ano' => 'required|integer|min:2020|max:2050',
            'meta_valor' => 'nullable|numeric|min:0',
            'meta_unidades' => 'nullable|integer|min:0',
            'id_proyecto' => 'nullable',
            'id_empleado' => 'nullable|exists:empleados,id_empleado',
        ]);

        Meta::create($validated);

        return back()->with('success', 'Meta creada correctamente');
    }

    public function edit($id)
    {
        $meta = Meta::with(['proyecto', 'empleado'])->findOrFail($id);

        return Inertia::render('Gerencia/Metas/Edit', [
            'meta' => $meta,
            'proyectos' => Proyecto::select('id_proyecto', 'nombre')->get(),
            'empleados' => Empleado::select('id_empleado', 'nombre', 'apellido')->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $meta = Meta::findOrFail($id);

        $validated = $request->validate([
            'tipo' => 'required',
            'mes' => 'required|integer|min:1|max:12',
            'ano' => 'required|integer|min:2020|max:2050',
            'meta_valor' => 'nullable|numeric|min:0',
            'meta_unidades' => 'nullable|integer|min:0',
            'id_proyecto' => 'nullable|exists:proyectos,id_proyecto',
            'id_empleado' => 'nullable|exists:empleados,id_empleado',
        ]);

        $meta->update($validated);

        return redirect()
            ->route('gerencia.metas.index')
            ->with('success', 'Meta actualizada correctamente');
    }

    public function destroy($id)
    {
        Meta::findOrFail($id)->delete();
        return back()->with('success', 'Meta eliminada');
    }
}
