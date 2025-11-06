<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PisoTorre;
use App\Models\Proyecto;
use App\Models\Torre;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PisoTorreWebController extends Controller
{
    public function index()
    {
        $pisos = \App\Models\PisoTorre::with(['torre.proyecto'])
            ->withCount('apartamentos') // <-- conteo
            ->orderBy('id_piso_torre', 'desc')
            ->get()
            ->map(function ($p) {
                return [
                    'id_piso_torre' => $p->id_piso_torre,
                    'nivel' => $p->nivel,
                    'uso' => $p->uso,
                    'torre' => $p->torre?->nombre_torre,
                    'proyecto' => $p->torre?->proyecto?->nombre,
                    'id_torre' => $p->id_torre,
                    'apartamentos_count' => $p->apartamentos_count, // <-- enviar al front
                ];
            });

        return \Inertia\Inertia::render('Admin/PisoTorre/Index', [
            'pisos' => $pisos,
        ]);
    }

    public function create()
    {
        // Proyectos para el select; torres se cargan on-demand por proyecto
        $proyectos = Proyecto::select('id_proyecto', 'nombre')->orderBy('nombre')->get();

        return Inertia::render('Admin/PisoTorre/Create', [
            'proyectos' => $proyectos,
        ]);
    }

    public function store(Request $request)
    {
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

        // Validación de duplicados por (torre, nivel)
        $exists = PisoTorre::where('id_torre', $validated['id_torre'])
            ->where('nivel', $validated['nivel'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['nivel' => 'Ya existe un piso con este nivel en la torre seleccionada'])->withInput();
        }

        PisoTorre::create($validated);

        return redirect()->route('pisostorre.index')->with('success', 'Piso creado exitosamente');
    }

    public function show($id)
    {
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
        ]);
    }

    public function edit($id)
    {
        $piso = PisoTorre::with(['torre.proyecto'])->findOrFail($id);

        $proyectos = Proyecto::select('id_proyecto', 'nombre')->orderBy('nombre')->get();

        // Torres del proyecto actual para precargar el segundo select
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

    // Endpoint auxiliar para cargar torres por proyecto (para los selects encadenados)
    public function torresPorProyecto($id_proyecto)
    {
        $torres = Torre::where('id_proyecto', $id_proyecto)
            ->select('id_torre', 'nombre_torre', 'id_proyecto')
            ->orderBy('nombre_torre')
            ->get();

        return response()->json($torres);
    }
}
