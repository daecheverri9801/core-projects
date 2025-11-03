<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CargoController extends Controller
{
    /**
     * Listar todos los cargos
     */
    public function index()
    {
        $cargos = Cargo::with('empleados')->get();

        return response()->json([
            'success' => true,
            'data' => $cargos
        ], 200);
    }

    /**
     * Crear un nuevo cargo
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:80|unique:cargo,nombre',
            'descripcion' => 'nullable|string|max:200'
        ], [
            'nombre.required' => 'El nombre del cargo es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 80 caracteres',
            'nombre.unique' => 'Este cargo ya existe en el sistema',
            'descripcion.max' => 'La descripción no puede exceder 200 caracteres'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $cargo = Cargo::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Cargo creado exitosamente',
            'data' => $cargo
        ], 201);
    }

    /**
     * Mostrar un cargo específico
     */
    public function show(string $id)
    {
        $cargo = Cargo::with('empleados.dependencia')->find($id);

        if (!$cargo) {
            return response()->json([
                'success' => false,
                'message' => 'Cargo no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $cargo
        ], 200);
    }

    /**
     * Actualizar un cargo
     */
    public function update(Request $request, string $id)
    {
        $cargo = Cargo::find($id);

        if (!$cargo) {
            return response()->json([
                'success' => false,
                'message' => 'Cargo no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:80|unique:cargo,nombre,' . $id . ',id_cargo',
            'descripcion' => 'nullable|string|max:200'
        ], [
            'nombre.required' => 'El nombre del cargo es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 80 caracteres',
            'nombre.unique' => 'Este cargo ya existe en el sistema',
            'descripcion.max' => 'La descripción no puede exceder 200 caracteres'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $cargo->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Cargo actualizado exitosamente',
            'data' => $cargo
        ], 200);
    }

    /**
     * Eliminar un cargo
     */
    public function destroy(string $id)
    {
        $cargo = Cargo::find($id);

        if (!$cargo) {
            return response()->json([
                'success' => false,
                'message' => 'Cargo no encontrado'
            ], 404);
        }

        // Verificar si tiene empleados asociados
        if ($cargo->empleados()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el cargo porque tiene empleados asociados'
            ], 409);
        }

        $cargo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cargo eliminado exitosamente'
        ], 200);
    }

    /**
     * Listar cargos con conteo de empleados
     */
    public function withCount()
    {
        $cargos = Cargo::withCount('empleados')->get();

        return response()->json([
            'success' => true,
            'data' => $cargos
        ], 200);
    }

    /**
     * Listar empleados por cargo
     */
    public function empleados(string $id)
    {
        $cargo = Cargo::with(['empleados' => function ($query) {
            $query->where('estado', true);
        }, 'empleados.dependencia'])->find($id);

        if (!$cargo) {
            return response()->json([
                'success' => false,
                'message' => 'Cargo no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'cargo' => $cargo->nombre,
                'descripcion' => $cargo->descripcion,
                'empleados' => $cargo->empleados
            ]
        ], 200);
    }
}