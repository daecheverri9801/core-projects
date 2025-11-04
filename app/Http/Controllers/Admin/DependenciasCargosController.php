<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dependencia;
use App\Models\Cargo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DependenciasCargosController extends Controller
{
    public function index()
    {
        $dependencias = Dependencia::withCount('empleados')->orderBy('nombre')->get();
        $cargos = Cargo::withCount('empleados')->orderBy('nombre')->get();

        return Inertia::render('Admin/DependenciasCargos/Index', [
            'dependencias' => $dependencias,
            'cargos' => $cargos,
        ]);
    }

    // Dependencias CRUD

    public function storeDependencia(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:80|unique:dependencias,nombre',
            'descripcion' => 'nullable|string|max:200',
        ], [
            'nombre.required' => 'El nombre de la dependencia es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 80 caracteres',
            'nombre.unique' => 'Esta dependencia ya existe en el sistema',
            'descripcion.max' => 'La descripción no puede exceder 200 caracteres',
        ]);

        Dependencia::create($validated);

        return redirect()->back()->with('success', 'Dependencia creada exitosamente');
    }

    public function updateDependencia(Request $request, $id)
    {
        $dependencia = Dependencia::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:80|unique:dependencias,nombre,' . $id . ',id_dependencia',
            'descripcion' => 'nullable|string|max:200',
        ], [
            'nombre.required' => 'El nombre de la dependencia es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 80 caracteres',
            'nombre.unique' => 'Esta dependencia ya existe en el sistema',
            'descripcion.max' => 'La descripción no puede exceder 200 caracteres',
        ]);

        $dependencia->update($validated);

        return redirect()->back()->with('success', 'Dependencia actualizada exitosamente');
    }

    public function destroyDependencia($id)
    {
        $dependencia = Dependencia::findOrFail($id);

        if ($dependencia->empleados()->count() > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar la dependencia porque tiene empleados asociados');
        }

        $dependencia->delete();

        return redirect()->back()->with('success', 'Dependencia eliminada exitosamente');
    }

    // Cargos CRUD

    public function storeCargo(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:80|unique:cargos,nombre',
            'descripcion' => 'nullable|string|max:200',
        ], [
            'nombre.required' => 'El nombre del cargo es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 80 caracteres',
            'nombre.unique' => 'Este cargo ya existe en el sistema',
            'descripcion.max' => 'La descripción no puede exceder 200 caracteres',
        ]);

        Cargo::create($validated);

        return redirect()->back()->with('success', 'Cargo creado exitosamente');
    }

    public function updateCargo(Request $request, $id)
    {
        $cargo = Cargo::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:80|unique:cargos,nombre,' . $id . ',id_cargo',
            'descripcion' => 'nullable|string|max:200',
        ], [
            'nombre.required' => 'El nombre del cargo es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 80 caracteres',
            'nombre.unique' => 'Este cargo ya existe en el sistema',
            'descripcion.max' => 'La descripción no puede exceder 200 caracteres',
        ]);

        $cargo->update($validated);

        return redirect()->back()->with('success', 'Cargo actualizado exitosamente');
    }

    public function destroyCargo($id)
    {
        $cargo = Cargo::findOrFail($id);

        if ($cargo->empleados()->count() > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar el cargo porque tiene empleados asociados');
        }

        $cargo->delete();

        return redirect()->back()->with('success', 'Cargo eliminado exitosamente');
    }
}