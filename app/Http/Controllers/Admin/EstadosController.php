<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Estado;
use App\Models\EstadoInmueble;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

class EstadosController extends Controller
{
    // Mostrar listado combinado
    public function index()
    {
        $estados = Estado::with(['proyectos', 'torres'])->orderBy('nombre')->get();
        $estadosInmueble = EstadoInmueble::with('apartamentos', 'locales')->orderBy('nombre')->get();

        return Inertia::render('Admin/Estados/Index', [
            'estados' => $estados,
            'estadosInmueble' => $estadosInmueble,
        ]);
    }

    // Crear Estado
    public function storeEstado(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:50|unique:estados,nombre',
            'descripcion' => 'nullable|string|max:200',
        ], [
            'nombre.required' => 'El nombre del estado es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 50 caracteres',
            'nombre.unique' => 'Este estado ya existe en el sistema',
            'descripcion.max' => 'La descripción no puede exceder 200 caracteres',
        ]);

        Estado::create($validated);

        return redirect()->back()->with('success', 'Estado creado exitosamente');
    }

    // Actualizar Estado
    public function updateEstado(Request $request, $id)
    {
        $estado = Estado::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:50|unique:estados,nombre,' . $id . ',id_estado',
            'descripcion' => 'nullable|string|max:200',
        ], [
            'nombre.required' => 'El nombre del estado es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 50 caracteres',
            'nombre.unique' => 'Este estado ya existe en el sistema',
            'descripcion.max' => 'La descripción no puede exceder 200 caracteres',
        ]);

        $estado->update($validated);

        return redirect()->back()->with('success', 'Estado actualizado exitosamente');
    }

    // Eliminar Estado
    public function destroyEstado($id)
    {
        $estado = Estado::findOrFail($id);

        if ($estado->proyectos()->count() > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar el estado porque tiene proyectos asociados');
        }

        $estado->delete();

        return redirect()->back()->with('success', 'Estado eliminado exitosamente');
    }

    // Crear Estado Inmueble
    public function storeEstadoInmueble(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:50|unique:estados_inmueble,nombre',
            'descripcion' => 'nullable|string|max:200',
        ], [
            'nombre.required' => 'El nombre del estado de inmueble es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 50 caracteres',
            'nombre.unique' => 'Este estado de inmueble ya existe en el sistema',
            'descripcion.max' => 'La descripción no puede exceder 200 caracteres',
        ]);

        EstadoInmueble::create($validated);

        return redirect()->back()->with('success', 'Estado de inmueble creado exitosamente');
    }

    // Actualizar Estado Inmueble
    public function updateEstadoInmueble(Request $request, $id)
    {
        $estadoInmueble = EstadoInmueble::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:50|unique:estados_inmueble,nombre,' . $id . ',id_estado_inmueble',
            'descripcion' => 'nullable|string|max:200',
        ], [
            'nombre.required' => 'El nombre del estado de inmueble es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 50 caracteres',
            'nombre.unique' => 'Este estado de inmueble ya existe en el sistema',
            'descripcion.max' => 'La descripción no puede exceder 200 caracteres',
        ]);

        $estadoInmueble->update($validated);

        return redirect()->back()->with('success', 'Estado de inmueble actualizado exitosamente');
    }

    // Eliminar Estado Inmueble
    public function destroyEstadoInmueble($id)
    {
        $estadoInmueble = EstadoInmueble::findOrFail($id);

        $apartamentosCount = $estadoInmueble->apartamentos()->count();
        $localesCount = $estadoInmueble->locales()->count();

        if ($apartamentosCount > 0 || $localesCount > 0) {
            $msg = 'No se puede eliminar el estado de inmueble porque tiene ';
            $msg .= $apartamentosCount > 0 ? $apartamentosCount . ' apartamento(s)' : '';
            $msg .= ($apartamentosCount > 0 && $localesCount > 0) ? ' y ' : '';
            $msg .= $localesCount > 0 ? $localesCount . ' local(es)' : '';
            $msg .= ' asociado(s)';
            return redirect()->back()->with('error', $msg);
        }

        $estadoInmueble->delete();

        return redirect()->back()->with('success', 'Estado de inmueble eliminado exitosamente');
    }
}
