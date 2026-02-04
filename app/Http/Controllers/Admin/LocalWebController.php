<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Local;
use App\Models\Proyecto;
use App\Models\Torre;
use App\Models\PisoTorre;
use App\Models\EstadoInmueble;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LocalWebController extends Controller
{
    public function index(Request $request)
    {
        $empleado = $request->user()->load('cargo');
        $search = trim((string) $request->get('search'));

        // Ajusta los nombres de relaciones/modelos según tu proyecto:
        // Proyecto -> torres -> locales
        $proyectos = Proyecto::query()
            ->select('id_proyecto', 'nombre')
            ->when($search, function ($q) use ($search) {
                $q->whereHas('torres.locales', function ($lq) use ($search) {
                    // Campos de Local (ajusta a tu tabla real)
                    $lq->where(function ($w) use ($search) {
                        $w->where('numero', 'ILIKE', "%{$search}%")
                            ->orWhere('valor_total', '::text', 'ILIKE', "%{$search}%"); // si aplica
                    });

                    // Si quieres que también busque por estado/piso/torre:
                    $lq->orWhereHas('torre', fn($tq) => $tq->where('nombre_torre', 'ILIKE', "%{$search}%"));
                    $lq->orWhereHas('pisoTorre', fn($pq) => $pq->whereRaw('CAST(nivel AS TEXT) ILIKE ?', ["%{$search}%"]));
                    $lq->orWhereHas('estadoInmueble', fn($eq) => $eq->where('nombre', 'ILIKE', "%{$search}%"));
                });
            })
            ->with([
                'torres:id_torre,nombre_torre,id_proyecto',
                'torres.locales' => function ($lq) use ($search) {
                    // Ajusta columnas reales de Local
                    $lq->select(
                        'id_local',
                        'numero',
                        'id_torre',
                        'id_piso_torre',
                        'id_estado_inmueble',
                        'area_total_local',
                        'valor_m2',
                        'valor_total'
                    )
                        ->with([
                            'torre:id_torre,nombre_torre',
                            'pisoTorre:id_piso_torre,nivel',
                            'estadoInmueble:id_estado_inmueble,nombre',
                        ])
                        ->when($search, function ($q) use ($search) {
                            $q->where(function ($w) use ($search) {
                                $w->where('numero', 'ILIKE', "%{$search}%")
                                    ->orWhereRaw('CAST(area_total_local AS TEXT) ILIKE ?', ["%{$search}%"])
                                    ->orWhereRaw('CAST(valor_m2 AS TEXT) ILIKE ?', ["%{$search}%"])
                                    ->orWhereRaw('CAST(valor_total AS TEXT) ILIKE ?', ["%{$search}%"]);
                            });
                        })
                        ->orderBy('id_local', 'desc');
                },
            ])
            ->withCount(['torres as locales_count' => function ($q) {
                // cuenta total de locales del proyecto (ajusta si tu relación difiere)
                $q->join('locales', 'locales.id_torre', '=', 'torres.id_torre');
            }])
            ->orderBy('id_proyecto', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Aplanar locales por proyecto para que el front no haga lógica extra
        $proyectos->getCollection()->transform(function ($p) {
            $locales = collect($p->torres ?? [])
                ->flatMap(function ($t) {
                    return collect($t->locales ?? [])->map(function ($l) use ($t) {
                        return [
                            'id_local' => $l->id_local,
                            'numero' => $l->numero,
                            'torre' => $t->nombre_torre,
                            'piso' => $l->pisoTorre?->nivel,
                            'estado' => $l->estadoInmueble?->nombre,
                            'area_total_local' => $l->area_total_local,
                            'valor_m2' => $l->valor_m2,
                            'valor_total' => $l->valor_total,
                        ];
                    });
                })
                ->values();

            return [
                'id_proyecto' => $p->id_proyecto,
                'nombre' => $p->nombre,
                'locales_count' => $locales->count(),
                'locales' => $locales,
            ];
        });

        return Inertia::render('Admin/Local/Index', [
            'proyectos' => $proyectos,
            'filters' => ['search' => $search],
            'empleado' => $empleado,
        ]);
    }



    public function create(Request $request)
    {
        $empleado = $request->user()->load('cargo');
        return Inertia::render('Admin/Local/Create', [
            'proyectos' => Proyecto::select('id_proyecto', 'nombre')->orderBy('nombre')->get(),
            'estados' => EstadoInmueble::select('id_estado_inmueble', 'nombre')->orderBy('nombre')->get(),
            'empleado' => $empleado,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero' => ['required', 'string', 'max:20'],
            'id_estado_inmueble' => ['required', 'exists:estados_inmueble,id_estado_inmueble'],
            'area_total_local' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'id_torre' => ['required', 'exists:torres,id_torre'],
            'id_piso_torre' => ['required', 'exists:pisos_torre,id_piso_torre'],
            'valor_m2' => ['nullable', 'numeric', 'min:0', 'max:9999999999999999.99'],
        ], [
            'numero.required' => 'El número del local es obligatorio',
            'numero.max' => 'El número del local no puede exceder 20 caracteres',
            'id_estado_inmueble.required' => 'El estado del inmueble es obligatorio',
            'id_estado_inmueble.exists' => 'El estado del inmueble seleccionado no existe',
            'area_total_local.numeric' => 'El área total debe ser un número',
            'area_total_local.min' => 'El área total no puede ser negativa',
            'area_total_local.max' => 'El área total excede el límite permitido',
            'id_torre.required' => 'La torre es obligatoria',
            'id_torre.exists' => 'La torre seleccionada no existe',
            'id_piso_torre.required' => 'El piso es obligatorio',
            'id_piso_torre.exists' => 'El piso seleccionado no existe',
        ]);

        // Calcular valor_total en backend
        $area = isset($validated['area_total_local']) ? (float)$validated['area_total_local'] : null;
        $valorM2 = isset($validated['valor_m2']) ? (float)$validated['valor_m2'] : null;
        $validated['valor_total'] = ($area !== null && $valorM2 !== null) ? round($area * $valorM2, 2) : null;

        // Coherencia: piso pertenece a torre
        $piso = PisoTorre::find($validated['id_piso_torre']);
        if ($piso && $piso->id_torre != $validated['id_torre']) {
            return back()->withErrors(['id_piso_torre' => 'El piso seleccionado no pertenece a la torre indicada'])->withInput();
        }

        // Unicidad: número dentro de la misma torre
        $exists = Local::where('numero', $validated['numero'])
            ->where('id_torre', $validated['id_torre'])
            ->exists();
        if ($exists) {
            return back()->withErrors(['numero' => 'Ya existe un local con este número en la torre seleccionada'])->withInput();
        }

        Local::create($validated);

        return redirect()->route('locales.index')->with('success', 'Local creado exitosamente');
    }

    public function show(Request $request, $id)
    {
        $empleado = $request->user()->load('cargo');
        $local = Local::with(['estadoInmueble', 'torre.proyecto.ubicacion.ciudad', 'pisoTorre'])
            ->findOrFail($id);

        // Resumen tipo API
        return Inertia::render('Admin/Local/Show', [
            'local' => $local,
            'resumen' => [
                'id_local' => $local->id_local,
                'numero' => $local->numero,
                'area_total' => $local->area_total_local,
                'torre' => $local->torre?->nombre_torre,
                'piso' => $local->pisoTorre?->nivel,
                'proyecto' => $local->torre?->proyecto?->nombre,
                'ubicacion' => optional($local->torre?->proyecto?->ubicacion, function ($u) {
                    $ciudad = $u->ciudad?->nombre ?? '';
                    return trim(($u->direccion ?? '') . (strlen($ciudad) ? ', ' . $ciudad : ''));
                }),
                'estado' => $local->estadoInmueble?->nombre,
                'valor_total' => $local->valor_total,
                'valor_m2' => $local->valor_m2,
            ],
            'empleado' => $empleado,
        ]);
    }

    public function edit(Request $request, $id)
    {
        $empleado = $request->user()->load('cargo');
        $l = Local::with(['torre.proyecto', 'pisoTorre'])->findOrFail($id);

        // Pre-cargas
        $idProyecto = $l->torre?->id_proyecto;
        $torres = $idProyecto
            ? Torre::where('id_proyecto', $idProyecto)->select('id_torre', 'nombre_torre', 'id_proyecto')->orderBy('nombre_torre')->get()
            : [];
        $pisos = $l->id_torre
            ? PisoTorre::where('id_torre', $l->id_torre)->select('id_piso_torre', 'nivel', 'id_torre')->orderBy('nivel')->get()
            : [];

        return Inertia::render('Admin/Local/Edit', [
            'local' => [
                'id_local' => $l->id_local,
                'numero' => $l->numero,
                'id_estado_inmueble' => $l->id_estado_inmueble,
                'area_total_local' => $l->area_total_local,
                'id_torre' => $l->id_torre,
                'id_piso_torre' => $l->id_piso_torre,
                'valor_m2' => $l->valor_m2,
                'valor_total' => $l->valor_total,
                'id_proyecto' => $idProyecto,
            ],
            'proyectos' => Proyecto::select('id_proyecto', 'nombre')->orderBy('nombre')->get(),
            'estados' => EstadoInmueble::select('id_estado_inmueble', 'nombre')->orderBy('nombre')->get(),
            'torresInicial' => $torres,
            'pisosInicial' => $pisos,
            'empleado' => $empleado,
        ]);
    }

    public function update(Request $request, $id)
    {
        $l = Local::findOrFail($id);

        $validated = $request->validate([
            'numero' => ['required', 'string', 'max:20'],
            'id_estado_inmueble' => ['required', 'exists:estados_inmueble,id_estado_inmueble'],
            'area_total_local' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'id_torre' => ['required', 'exists:torres,id_torre'],
            'id_piso_torre' => ['required', 'exists:pisos_torre,id_piso_torre'],
            'valor_m2' => ['nullable', 'numeric', 'min:0', 'max:9999999999999999.99'],
        ], [
            'numero.required' => 'El número del local es obligatorio',
            'numero.max' => 'El número del local no puede exceder 20 caracteres',
            'id_estado_inmueble.required' => 'El estado del inmueble es obligatorio',
            'id_estado_inmueble.exists' => 'El estado del inmueble seleccionado no existe',
            'area_total_local.numeric' => 'El área total debe ser un número',
            'area_total_local.min' => 'El área total no puede ser negativa',
            'area_total_local.max' => 'El área total excede el límite permitido',
            'id_torre.required' => 'La torre es obligatoria',
            'id_torre.exists' => 'La torre seleccionada no existe',
            'id_piso_torre.required' => 'El piso es obligatorio',
            'id_piso_torre.exists' => 'El piso seleccionado no existe',
        ]);

        // Calcular valor_total en backend
        $area = isset($validated['area_total_local']) ? (float)$validated['area_total_local'] : null;
        $valorM2 = isset($validated['valor_m2']) ? (float)$validated['valor_m2'] : null;
        $validated['valor_total'] = ($area !== null && $valorM2 !== null) ? round($area * $valorM2, 2) : null;

        // Coherencia: piso pertenece a torre
        $piso = PisoTorre::find($validated['id_piso_torre']);
        if ($piso && $piso->id_torre != $validated['id_torre']) {
            return back()->withErrors(['id_piso_torre' => 'El piso seleccionado no pertenece a la torre indicada'])->withInput();
        }

        // Unicidad: número dentro de la misma torre (excluyendo el actual)
        $exists = Local::where('numero', $validated['numero'])
            ->where('id_torre', $validated['id_torre'])
            ->where('id_local', '!=', $l->id_local)
            ->exists();
        if ($exists) {
            return back()->withErrors(['numero' => 'Ya existe otro local con este número en la torre seleccionada'])->withInput();
        }

        $l->update($validated);

        return redirect()->route('locales.show', $l->id_local)->with('success', 'Local actualizado exitosamente');
    }

    public function destroy($id)
    {
        $l = Local::findOrFail($id);
        $l->delete();

        return redirect()->route('locales.index')->with('success', 'Local eliminado exitosamente');
    }

    // Auxiliares Selects encadenados
    public function torresPorProyecto($id_proyecto)
    {
        return Torre::with('proyecto:id_proyecto,prima_altura_base,prima_altura_incremento,prima_altura_activa')
            ->where('id_proyecto', $id_proyecto)
            ->select('id_torre', 'nombre_torre', 'id_proyecto', 'nivel_inicio_prima')
            ->orderBy('nombre_torre')
            ->get();
    }

    public function pisosPorTorre($id_torre)
    {
        return PisoTorre::where('id_torre', $id_torre)
            ->select('id_piso_torre', 'nivel', 'id_torre')
            ->orderBy('nivel')
            ->get();
    }

    public function scopeDisponiblesPorProyecto($query, int $idProyecto)
    {
        return $query
            ->where('id_estado_inmueble', function ($q) {
                $q->select('id_estado_inmueble')
                    ->from('estados_inmueble')
                    ->where('nombre', 'Disponible')
                    ->limit(1);
            })
            ->whereHas('torre', function ($q) use ($idProyecto) {
                $q->where('id_proyecto', $idProyecto);
            });
    }
}
