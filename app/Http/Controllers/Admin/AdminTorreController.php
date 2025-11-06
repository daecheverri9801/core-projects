<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Torre;
use App\Models\Proyecto;
use App\Models\Estado;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AdminTorreController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $query = Torre::with(['proyecto', 'estado'])
            ->when($search, function ($q) use ($search) {
                // Para PostgreSQL puedes usar ILIKE
                $q->where('nombre_torre', 'ILIKE', '%' . $search . '%');
            })
            ->orderBy('id_torre', 'desc');

        $torres = $query->paginate(10)->withQueryString();

        return Inertia::render('Admin/Torres/Index', [
            'torres' => $torres,
            'filters' => ['search' => $search],
            // En tus props globales ya viene auth.empleado, pero aquí no está de más:
            'empleado' => auth()->user()->empleado ?? null,
        ]);
    }

    public function create()
    {
        $proyectos = Proyecto::select('id_proyecto', 'nombre')->orderBy('nombre')->get();
        $estados = Estado::select('id_estado', 'nombre')->orderBy('nombre')->get();

        return Inertia::render('Admin/Torres/Create', [
            'proyectos' => $proyectos,
            'estados' => $estados,
            'empleado' => auth()->user()->empleado ?? null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_torre' => ['required', 'string', 'max:50'],
            'numero_pisos' => ['nullable', 'integer', 'min:1', 'max:32767'],
            'id_proyecto' => ['required', Rule::exists('proyectos', 'id_proyecto')],
            'id_estado' => ['required', Rule::exists('estados', 'id_estado')],
        ]);

        $torre = Torre::create($validated);

        return redirect()
            ->route('admin.torres.show', $torre->id_torre)
            ->with('success', 'Torre creada exitosamente');
    }

    public function show($id_torre)
    {
        $torre = Torre::with([
            'proyecto.ubicacion.ciudad',
            'estado',
            'pisos',
            'apartamentos.tipoApartamento',
            'apartamentos.estadoInmueble'
        ])->findOrFail($id_torre);

        return Inertia::render('Admin/Torres/Show', [
            'torre' => $torre,
            'empleado' => auth()->user()->empleado ?? null,
        ]);
    }

    public function edit($id_torre)
    {
        $torre = Torre::with(['proyecto', 'estado'])->findOrFail($id_torre);

        $proyectos = Proyecto::select('id_proyecto', 'nombre')->orderBy('nombre')->get();
        $estados = Estado::select('id_estado', 'nombre')->orderBy('nombre')->get();

        return Inertia::render('Admin/Torres/Edit', [
            'torre' => $torre,
            'proyectos' => $proyectos,
            'estados' => $estados,
            'empleado' => auth()->user()->empleado ?? null,
        ]);
    }

    public function update(Request $request, $id_torre)
    {
        $torre = Torre::findOrFail($id_torre);

        $validated = $request->validate([
            'nombre_torre' => ['required', 'string', 'max:50'],
            'numero_pisos' => ['nullable', 'integer', 'min:1', 'max:32767'],
            'id_proyecto' => ['required', Rule::exists('proyectos', 'id_proyecto')],
            'id_estado' => ['required', Rule::exists('estados', 'id_estado')],
        ]);

        $torre->update($validated);

        return redirect()
            ->route('admin.torres.show', $torre->id_torre)
            ->with('success', 'Torre actualizada exitosamente');
    }

    public function destroy($id_torre)
    {
        $torre = Torre::withCount(['pisos', 'apartamentos'])->findOrFail($id_torre);

        if ($torre->pisos_count > 0 || $torre->apartamentos_count > 0) {
            return back()->withErrors([
                'general' => 'No se puede eliminar la torre porque tiene ' .
                    ($torre->pisos_count > 0 ? $torre->pisos_count . ' piso(s)' : '') .
                    ($torre->pisos_count > 0 && $torre->apartamentos_count > 0 ? ' y ' : '') .
                    ($torre->apartamentos_count > 0 ? $torre->apartamentos_count . ' apartamento(s)' : '') . ' asociado(s)'
            ]);
        }

        $torre->delete();

        return redirect()
            ->route('admin.torres.index')
            ->with('success', 'Torre eliminada exitosamente');
    }
}
