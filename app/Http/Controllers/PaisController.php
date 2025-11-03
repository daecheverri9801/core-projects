<?php

namespace App\Http\Controllers;

use App\Models\Pais;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaisController extends Controller
{
    /**
     * Listar todos los países
     */
    public function index()
    {
        $paises = Pais::with('departamentos')->get();

        return response()->json([
            'success' => true,
            'data' => $paises
        ], 200);
    }

    /**
     * Crear un nuevo país
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:80|unique:pais,nombre'
        ], [
            'nombre.required' => 'El nombre del país es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 80 caracteres',
            'nombre.unique' => 'Este país ya existe en el sistema'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $pais = Pais::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'País creado exitosamente',
            'data' => $pais
        ], 201);
    }

    /**
     * Mostrar un país específico
     */
    public function show(string $id)
    {
        $pais = Pais::with('departamentos.ciudades')->find($id);

        if (!$pais) {
            return response()->json([
                'success' => false,
                'message' => 'País no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $pais
        ], 200);
    }

    /**
     * Actualizar un país
     */
    public function update(Request $request, string $id)
    {
        $pais = Pais::find($id);

        if (!$pais) {
            return response()->json([
                'success' => false,
                'message' => 'País no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:80|unique:pais,nombre,' . $id . ',id_pais'
        ], [
            'nombre.required' => 'El nombre del país es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 80 caracteres',
            'nombre.unique' => 'Este país ya existe en el sistema'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $pais->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'País actualizado exitosamente',
            'data' => $pais
        ], 200);
    }

    /**
     * Eliminar un país
     */
    public function destroy(string $id)
    {
        $pais = Pais::find($id);

        if (!$pais) {
            return response()->json([
                'success' => false,
                'message' => 'País no encontrado'
            ], 404);
        }

        // Verificar si tiene departamentos asociados
        if ($pais->departamentos()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el país porque tiene departamentos asociados'
            ], 409);
        }

        $pais->delete();

        return response()->json([
            'success' => true,
            'message' => 'País eliminado exitosamente'
        ], 200);
    }
}