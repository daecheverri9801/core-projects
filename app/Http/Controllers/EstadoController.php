<?php

namespace App\Http\Controllers;

use App\Models\Estado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EstadoController extends Controller
{
    /**
     * Listar todos los estados
     */
    public function index()
    {
        $estados = Estado::with('proyectos')->get();

        return response()->json([
            'success' => true,
            'data' => $estados
        ], 200);
    }

    /**
     * Crear un nuevo estado
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:50|unique:estado,nombre',
            'descripcion' => 'nullable|string|max:200'
        ], [
            'nombre.required' => 'El nombre del estado es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 50 caracteres',
            'nombre.unique' => 'Este estado ya existe en el sistema',
            'descripcion.max' => 'La descripción no puede exceder 200 caracteres'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $estado = Estado::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Estado creado exitosamente',
            'data' => $estado
        ], 201);
    }

    /**
     * Mostrar un estado específico
     */
    public function show(string $id)
    {
        $estado = Estado::with('proyectos')->find($id);

        if (!$estado) {
            return response()->json([
                'success' => false,
                'message' => 'Estado no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $estado
        ], 200);
    }

    /**
     * Actualizar un estado
     */
    public function update(Request $request, string $id)
    {
        $estado = Estado::find($id);

        if (!$estado) {
            return response()->json([
                'success' => false,
                'message' => 'Estado no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:50|unique:estado,nombre,' . $id . ',id_estado',
            'descripcion' => 'nullable|string|max:200'
        ], [
            'nombre.required' => 'El nombre del estado es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 50 caracteres',
            'nombre.unique' => 'Este estado ya existe en el sistema',
            'descripcion.max' => 'La descripción no puede exceder 200 caracteres'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $estado->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado exitosamente',
            'data' => $estado
        ], 200);
    }

    /**
     * Eliminar un estado
     */
    public function destroy(string $id)
    {
        $estado = Estado::find($id);

        if (!$estado) {
            return response()->json([
                'success' => false,
                'message' => 'Estado no encontrado'
            ], 404);
        }

        // Verificar si tiene proyectos asociados
        if ($estado->proyectos()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el estado porque tiene proyectos asociados'
            ], 409);
        }

        $estado->delete();

        return response()->json([
            'success' => true,
            'message' => 'Estado eliminado exitosamente'
        ], 200);
    }

    /**
     * Listar estados con conteo de proyectos
     */
    public function withCount()
    {
        $estados = Estado::withCount('proyectos')->get();

        return response()->json([
            'success' => true,
            'data' => $estados
        ], 200);
    }
}