<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ubicacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use App\Models\Pais;
use App\Models\Departamento;
use App\Models\Ciudad;

class UbicacionController extends Controller
{

    public function getJerarquia()
    {
        $paises = Pais::with('departamentos.ciudades')->get();

        return response()->json([
            'success' => true,
            'data' => $paises,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_ciudad' => 'required|exists:ciudades,id_ciudad',
            'barrio' => 'nullable|string|max:120',
            'direccion' => 'required|string|max:300',
        ], [
            'id_ciudad.required' => 'La ciudad es obligatoria',
            'id_ciudad.exists' => 'La ciudad seleccionada no existe',
            'barrio.max' => 'El barrio no puede exceder 120 caracteres',
            'direccion.required' => 'La dirección es obligatoria',
            'direccion.max' => 'La dirección no puede exceder 300 caracteres',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $ubicacion = Ubicacion::create($request->all());
        $ubicacion->load('ciudad.departamento.pais');

        // Aquí debes obtener los datos necesarios para la vista Create de Proyectos
        // Por ejemplo, estados y ubicaciones actualizadas
        $estados = \App\Models\Estado::all();
        $ubicaciones = Ubicacion::with('ciudad')->get();

        return Inertia::render('Admin/Proyectos/Create', [
            'estados' => $estados,
            'ubicaciones' => $ubicaciones,
            'empleado' => auth()->user()->empleado ?? null,
            'ubicacionNueva' => $ubicacion,
        ]);
    }
}
