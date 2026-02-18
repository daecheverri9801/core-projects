<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ZonaSocial;
use App\Models\Proyecto;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Support\RedirectBackTo;

class ZonaSocialWebController extends Controller
{
    public function index(Request $request)
    {
        $empleado = $request->user()->load('cargo');

        $zonas = ZonaSocial::with('proyecto.ubicacion.ciudad')
            ->orderBy('id_zona_social', 'desc')
            ->get()
            ->map(function ($z) {
                $u = $z->proyecto?->ubicacion;
                $ciudad = $u?->ciudad?->nombre ?? '';
                $direccion = $u?->direccion ?? '';

                return [
                    'id_zona_social' => $z->id_zona_social,
                    'nombre' => $z->nombre,
                    'descripcion' => $z->descripcion,
                    'proyecto' => $z->proyecto?->nombre,
                    'ubicacion' => trim($direccion . (strlen($ciudad) ? ', ' . $ciudad : '')),
                ];
            });

        return Inertia::render('Admin/ZonaSocial/Index', [
            'zonas' => $zonas,
            'empleado' => $empleado,
        ]);
    }

    public function create(Request $request)
    {
        $empleado = $request->user()->load('cargo');
        $proyectos = Proyecto::select('id_proyecto', 'nombre')->orderBy('nombre')->get();

        return Inertia::render('Admin/ZonaSocial/Create', [
            'proyectos' => $proyectos,
            'empleado' => $empleado,
            'proyecto_preseleccionado' => $request->query('proyecto'), // ✅ flujo
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:100',
            'id_proyecto' => 'required|exists:proyectos,id_proyecto',
        ], [
            'nombre.required' => 'El nombre de la zona social es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres',
            'descripcion.max' => 'La descripción no puede exceder 100 caracteres',
            'id_proyecto.required' => 'El proyecto es obligatorio',
            'id_proyecto.exists' => 'El proyecto seleccionado no existe',
        ]);

        $exists = ZonaSocial::where('nombre', $validated['nombre'])
            ->where('id_proyecto', $validated['id_proyecto'])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'nombre' => 'Ya existe una zona social con este nombre en el proyecto seleccionado'
            ])->withInput();
        }

        ZonaSocial::create($validated);

        return redirect()
            ->route('zonas-sociales.create', ['proyecto' => $validated['id_proyecto']])
            ->with('success', 'Zona social creada exitosamente');
    }

    public function show(Request $request, $id)
    {
        $empleado = $request->user()->load('cargo');
        $zona = ZonaSocial::with([
            'proyecto.ubicacion.ciudad.departamento.pais',
            'proyecto.estado_proyecto'
        ])->findOrFail($id);

        return Inertia::render('Admin/ZonaSocial/Show', [
            'zona' => $zona,
            'empleado' => $empleado,
        ]);
    }

    public function edit(Request $request, $id)
    {
        $empleado = $request->user()->load('cargo');
        $zona = ZonaSocial::findOrFail($id);
        $proyectos = Proyecto::select('id_proyecto', 'nombre')->orderBy('nombre')->get();

        return Inertia::render('Admin/ZonaSocial/Edit', [
            'zona' => $zona,
            'proyectos' => $proyectos,
            'empleado' => $empleado,
        ]);
    }

    public function update(Request $request, $id)
    {
        $zona = ZonaSocial::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:100',
            'id_proyecto' => 'required|exists:proyectos,id_proyecto',
        ]);

        $exists = ZonaSocial::where('nombre', $validated['nombre'])
            ->where('id_proyecto', $validated['id_proyecto'])
            ->where('id_zona_social', '!=', $id)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'nombre' => 'Ya existe otra zona social con este nombre en el proyecto seleccionado'
            ])->withInput();
        }

        $zona->update($validated);

        return redirect()->route('zonas-sociales.show', $zona->id_zona_social)
            ->with('success', 'Zona social actualizada exitosamente');
    }

    public function destroy($id)
    {
        $zona = ZonaSocial::findOrFail($id);
        $zona->delete();

        return redirect()->route('zonas-sociales.index')->with('success', 'Zona social eliminada');
    }
}
