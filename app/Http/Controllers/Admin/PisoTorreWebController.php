<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PisoTorre;
use App\Models\Proyecto;
use App\Models\Torre;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Support\RedirectBackTo;

class PisoTorreWebController extends Controller
{
    public function index(Request $request)
    {
        $empleado = $request->user()->load('cargo');
        $search = $request->get('search');

        /**
         * Paginación por PROYECTO y carga de pisos.
         * El filtro aplica sobre torre/nivel/uso.
         */
        $proyectosQuery = Proyecto::query()
            ->select('id_proyecto', 'nombre')
            ->with([
                'torres' => function ($q) {
                    // opcional: si en el futuro quieres usar esto en el header
                    $q->select('id_torre', 'id_proyecto', 'nombre_torre');
                },
                'torres.pisos' => function ($q) use ($search) {
                    $q->select('id_piso_torre', 'nivel', 'uso', 'id_torre')
                        ->withCount('apartamentos')
                        ->when($search, function ($qq) use ($search) {
                            $qq->where(function ($w) use ($search) {
                                $w->where('uso', 'ILIKE', '%' . $search . '%')
                                    ->orWhere('nivel', (int) $search === 0 ? '!=' : '=', is_numeric($search) ? (int)$search : -999999);
                            });
                        })
                        ->orderBy('nivel');
                },
            ])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('torres.pisos', function ($p) use ($search) {
                    $p->where('uso', 'ILIKE', '%' . $search . '%')
                        ->orWhereHas('torre', fn($t) => $t->where('nombre_torre', 'ILIKE', '%' . $search . '%'))
                        ->orWhere('nivel', is_numeric($search) ? (int)$search : -999999);
                })
                    ->orWhereHas('torres', fn($t) => $t->where('nombre_torre', 'ILIKE', '%' . $search . '%'));
            })
            ->orderBy('nombre');

        $proyectos = $proyectosQuery->paginate(2)->withQueryString();

        /**
         * Aplanar "torres.pisos" a "pisos" por proyecto para el front,
         * manteniendo el nombre de la torre y conteo de aptos.
         */
        $proyectos->getCollection()->transform(function ($proyecto) {
            $pisos = [];

            foreach ($proyecto->torres as $torre) {
                foreach ($torre->pisos as $p) {
                    $pisos[] = [
                        'id_piso_torre' => $p->id_piso_torre,
                        'nivel' => $p->nivel,
                        'uso' => $p->uso,
                        'torre' => $torre->nombre_torre,
                        'id_torre' => $torre->id_torre,
                        'apartamentos_count' => $p->apartamentos_count ?? 0,
                    ];
                }
            }

            // ordenar por torre + nivel (más estable en UI)
            usort($pisos, function ($a, $b) {
                $t = strcmp((string)$a['torre'], (string)$b['torre']);
                if ($t !== 0) return $t;
                return ((int)$a['nivel']) <=> ((int)$b['nivel']);
            });

            return [
                'id_proyecto' => $proyecto->id_proyecto,
                'nombre' => $proyecto->nombre,
                'pisos' => $pisos,
            ];
        });

        return Inertia::render('Admin/PisoTorre/Index', [
            'proyectos' => $proyectos,
            'filters' => ['search' => $search],
            'empleado' => $empleado,
        ]);
    }

    public function create(Request $request)
    {
        $empleado = $request->user()->load('cargo');
        $proyectos = Proyecto::select('id_proyecto', 'nombre')->orderBy('nombre')->get();

        return Inertia::render('Admin/PisoTorre/Create', [
            'proyectos' => $proyectos,
            'empleado' => $empleado,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_torre' => ['required', 'exists:torres,id_torre'],
            'pisos' => ['required', 'array', 'min:1'],
            'pisos.*.nivel' => ['required', 'integer', 'min:1'],
            'pisos.*.uso' => ['nullable', 'string', 'max:40'],
        ], [ /* tus mensajes */]);

        $idTorre = (int) $validated['id_torre'];
        $pisos = $validated['pisos'];

        $niveles = array_map(fn($p) => (int) $p['nivel'], $pisos);
        if (count($niveles) !== count(array_unique($niveles))) {
            return back()->withErrors(['pisos' => 'Hay niveles duplicados en la carga.'])->withInput();
        }

        $existing = PisoTorre::where('id_torre', $idTorre)
            ->whereIn('nivel', $niveles)
            ->pluck('nivel')
            ->all();

        if (!empty($existing)) {
            $errors = [];
            foreach ($pisos as $i => $p) {
                if (in_array((int)$p['nivel'], $existing, true)) {
                    $errors["pisos.$i.nivel"] = "Ya existe un piso con nivel {$p['nivel']} en la torre seleccionada.";
                }
            }
            return back()->withErrors($errors)->withInput();
        }

        DB::transaction(function () use ($idTorre, $pisos) {
            foreach ($pisos as $p) {
                PisoTorre::create([
                    'id_torre' => $idTorre,
                    'nivel' => (int) $p['nivel'],
                    'uso' => $p['uso'] ?? null,
                ]);
            }
        });

        return RedirectBackTo::respond(
            $request,
            'tipos-apartamento.create',
            [],
            'Pisos creados exitosamente'
        );
    }

    public function show(Request $request, $id)
    {
        $empleado = $request->user()->load('cargo');
        $piso = PisoTorre::with([
            'torre.proyecto',
            'apartamentos.tipoApartamento',
            'apartamentos.estadoInmueble',
        ])->findOrFail($id);

        return Inertia::render('Admin/PisoTorre/Show', [
            'piso' => $piso,
            'resumen' => [
                'total_apartamentos' => $piso->apartamentos->count(),
                'apartamentos_por_estado' => $piso->apartamentos->groupBy('id_estado_inmueble')->map(function ($g) {
                    return [
                        'estado' => $g->first()->estadoInmueble->nombre ?? 'Sin estado',
                        'cantidad' => $g->count(),
                    ];
                })->values(),
                'apartamentos_por_tipo' => $piso->apartamentos->groupBy('id_tipo_apartamento')->map(function ($g) {
                    return [
                        'tipo' => $g->first()->tipoApartamento->nombre ?? 'Sin tipo',
                        'cantidad' => $g->count(),
                    ];
                })->values(),
            ],
            'empleado' => $empleado,
        ]);
    }

    public function edit(Request $request, $id)
    {
        $empleado = $request->user()->load('cargo');
        $piso = PisoTorre::with(['torre.proyecto'])->findOrFail($id);

        $proyectos = Proyecto::select('id_proyecto', 'nombre')->orderBy('nombre')->get();

        $torres = [];
        if ($piso->torre && $piso->torre->id_proyecto) {
            $torres = Torre::where('id_proyecto', $piso->torre->id_proyecto)
                ->select('id_torre', 'nombre_torre', 'id_proyecto')
                ->orderBy('nombre_torre')
                ->get();
        }

        return Inertia::render('Admin/PisoTorre/Edit', [
            'piso' => [
                'id_piso_torre' => $piso->id_piso_torre,
                'nivel' => $piso->nivel,
                'uso' => $piso->uso,
                'id_torre' => $piso->id_torre,
                'id_proyecto' => $piso->torre?->id_proyecto,
            ],
            'proyectos' => $proyectos,
            'torresInicial' => $torres,
            'empleado' => $empleado,
        ]);
    }

    public function update(Request $request, $id)
    {
        $piso = PisoTorre::findOrFail($id);

        $validated = $request->validate([
            'nivel' => ['required', 'integer'],
            'id_torre' => ['required', 'exists:torres,id_torre'],
            'uso' => ['nullable', 'string', 'max:40'],
        ], [
            'nivel.required' => 'El nivel del piso es obligatorio',
            'nivel.integer' => 'El nivel debe ser un número entero',
            'id_torre.required' => 'La torre es obligatoria',
            'id_torre.exists' => 'La torre seleccionada no existe',
            'uso.max' => 'El uso no puede exceder 40 caracteres',
        ]);

        $exists = PisoTorre::where('id_torre', $validated['id_torre'])
            ->where('nivel', $validated['nivel'])
            ->where('id_piso_torre', '!=', $piso->id_piso_torre)
            ->exists();

        if ($exists) {
            return back()->withErrors(['nivel' => 'Ya existe otro piso con este nivel en la torre seleccionada'])->withInput();
        }

        $piso->update($validated);

        return redirect()->route('pisostorre.index')->with('success', 'Piso actualizado exitosamente');
    }

    public function destroy($id)
    {
        $piso = PisoTorre::withCount('apartamentos')->findOrFail($id);

        if ($piso->apartamentos_count > 0) {
            return back()->withErrors(['delete' => 'No se puede eliminar el piso porque tiene apartamentos asociados']);
        }

        $piso->delete();

        return redirect()->route('pisostorre.index')->with('success', 'Piso eliminado exitosamente');
    }

    public function torresPorProyecto($id_proyecto)
    {
        return Torre::with('proyecto:id_proyecto,prima_altura_base,prima_altura_incremento,prima_altura_activa')
            ->where('id_proyecto', $id_proyecto)
            ->select('id_torre', 'nombre_torre', 'id_proyecto', 'nivel_inicio_prima')
            ->orderBy('nombre_torre')
            ->get();
    }
}
