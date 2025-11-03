<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepartamentoController extends Controller
{
    /**
     * Listar todos los departamentos
     */
    public function index()
    {
        $departamentos = Departamento::with(['pais', 'ciudades'])->get();

        return response()->json([
            'success' => true,
            'data' => $departamentos
        ], 200);
    }

    /**
     * Crear un nuevo departamento
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'id_pais' => 'required|exists:pais,id_pais'
        ], [
            'nombre.required' => 'El nombre del departamento es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres',
            'id_pais.required' => 'El país es obligatorio',
            'id_pais.exists' => 'El país seleccionado no existe'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $departamento = Departamento::create($request->all());
        $departamento->load('pais');

        return response()->json([
            'success' => true,
            'message' => 'Departamento creado exitosamente',
            'data' => $departamento
        ], 201);
    }

    /**
     * Mostrar un departamento específico
     */
    public function show(string $id)
    {
        $departamento = Departamento::with(['pais', 'ciudades'])->find($id);

        if (!$departamento) {
            return response()->json([
                'success' => false,
                'message' => 'Departamento no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $departamento
        ], 200);
    }

    /**
     * Actualizar un departamento
     */
    public function update(Request $request, string $id)
    {
        $departamento = Departamento::find($id);

        if (!$departamento) {
            return response()->json([
                'success' => false,
                'message' => 'Departamento no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'id_pais' => 'required|exists:pais,id_pais'
        ], [
            'nombre.required' => 'El nombre del departamento es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres',
            'id_pais.required' => 'El país es obligatorio',
            'id_pais.exists' => 'El país seleccionado no existe'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $departamento->update($request->all());
        $departamento->load('pais');

        return response()->json([
            'success' => true,
            'message' => 'Departamento actualizado exitosamente',
            'data' => $departamento
        ], 200);
    }

    /**
     * Eliminar un departamento
     */
    public function destroy(string $id)
    {
        $departamento = Departamento::find($id);

        if (!$departamento) {
            return response()->json([
                'success' => false,
                'message' => 'Departamento no encontrado'
            ], 404);
        }

        // Verificar si tiene ciudades asociadas
        if ($departamento->ciudades()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el departamento porque tiene ciudades asociadas'
            ], 409);
        }

        $departamento->delete();

        return response()->json([
            'success' => true,
            'message' => 'Departamento eliminado exitosamente'
        ], 200);
    }

    /**
     * Listar departamentos por país
     */
    public function byPais(string $id_pais)
    {
        $departamentos = Departamento::where('id_pais', $id_pais)
            ->with('ciudades')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $departamentos
        ], 200);
    }
}