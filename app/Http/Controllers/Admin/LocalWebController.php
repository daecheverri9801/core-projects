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
    public function index()
    {
        $locales = Local::with(['estadoInmueble', 'torre.proyecto', 'pisoTorre'])
            ->orderBy('id_local', 'desc')
            ->get()
            ->map(function ($l) {
                return [
                    'id_local' => $l->id_local,
                    'numero' => $l->numero,
                    'estado' => $l->estadoInmueble?->nombre,
                    'proyecto' => $l->torre?->proyecto?->nombre,
                    'torre' => $l->torre?->nombre_torre,
                    'piso' => $l->pisoTorre?->nivel,
                    'area_total_local' => $l->area_total_local,
                    'valor_m2' => $l->valor_m2,
                    'valor_total' => $l->valor_total,
                ];
            });

        return Inertia::render('Admin/Local/Index', [
            'locales' => $locales,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Local/Create', [
            'proyectos' => Proyecto::select('id_proyecto', 'nombre')->orderBy('nombre')->get(),
            'estados' => EstadoInmueble::select('id_estado_inmueble', 'nombre')->orderBy('nombre')->get(),
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

    public function show($id)
    {
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
        ]);
    }

    public function edit($id)
    {
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
        return Torre::where('id_proyecto', $id_proyecto)
            ->select('id_torre', 'nombre_torre', 'id_proyecto')
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
