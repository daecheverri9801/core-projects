<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Http\Controllers\Controller;
use App\Models\PoliticaPrecioProyecto;
use App\Models\Proyecto;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Support\RedirectBackTo;

class PoliticaPrecioProyectoWebController extends Controller
{
    public function index(Request $request)
    {
        $empleado = $request->user()->load('cargo');
        $politicas = PoliticaPrecioProyecto::with('proyecto')
            ->orderBy('aplica_desde', 'desc')
            ->get();

        return Inertia::render('Admin/PoliticaPrecioProyecto/Index', [
            'politicas' => $politicas,
            'empleado' => $empleado,
        ]);
    }

    public function create(Request $request)
    {
        $empleado = $request->user()->load('cargo');
        $proyectos = Proyecto::orderBy('nombre')->get(['id_proyecto', 'nombre']);
        $proyectoSeleccionado = $request->query('proyecto');
        return Inertia::render('Admin/PoliticaPrecioProyecto/Create', [
            'proyectos' => $proyectos,
            'proyectoSeleccionado' => $proyectoSeleccionado,
            'empleado' => $empleado,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_proyecto' => 'required|exists:proyectos,id_proyecto',
            'ventas_por_escalon' => 'nullable|integer|min:1',
            'porcentaje_aumento' => 'nullable|numeric|min:0|max:999.999',
            'aplica_desde' => 'nullable|date',
            'estado' => 'boolean'
        ]);

        $politica = PoliticaPrecioProyecto::create($validated);

        return RedirectBackTo::respond(
            $request,
            'politicas-precio-proyecto.show',
            [$politica->id_politica_precio],
            'Política de precio creada exitosamente.',
            ['id_politica_precio' => $politica->id_politica_precio]
        );
    }

    public function show(Request $request, $id)
    {
        $empleado = $request->user()->load('cargo');
        $politica = PoliticaPrecioProyecto::with('proyecto')->findOrFail($id);
        return Inertia::render('Admin/PoliticaPrecioProyecto/Show', [
            'politica' => $politica,
            'empleado' => $empleado,
        ]);
    }

    public function edit(Request $request, $id)
    {
        $empleado = $request->user()->load('cargo');
        $politica = PoliticaPrecioProyecto::findOrFail($id);
        $proyectos = Proyecto::orderBy('nombre')->get(['id_proyecto', 'nombre']);

        return Inertia::render('Admin/PoliticaPrecioProyecto/Edit', [
            'politica' => $politica,
            'proyectos' => $proyectos,
            'empleado' => $empleado,
        ]);
    }

    public function update(Request $request, $id)
    {
        $politica = PoliticaPrecioProyecto::findOrFail($id);

        $validated = $request->validate([
            'id_proyecto' => 'required|exists:proyectos,id_proyecto',
            'ventas_por_escalon' => 'nullable|integer|min:1',
            'porcentaje_aumento' => 'nullable|numeric|min:0|max:999.999',
            'aplica_desde' => 'nullable|date',
            'estado' => 'boolean'
        ]);

        $politica->update($validated);

        return redirect()->route('politicas-precio-proyecto.show', $politica->id_politica_precio)
            ->with('success', 'Política de precio actualizada exitosamente.');
    }

    public function destroy($id)
    {
        $politica = PoliticaPrecioProyecto::findOrFail($id);
        $politica->delete();
        return redirect()->route('politicas-precio-proyecto.index')
            ->with('success', 'Política de precio eliminada exitosamente.');
    }
}
