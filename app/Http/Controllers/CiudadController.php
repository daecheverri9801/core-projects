<?php

namespace App\Http\Controllers;

use App\Models\Ciudad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CiudadController extends Controller
{
    /**
     * Listar todas las ciudades
     */
    public function index()
    {
        $ciudades = Ciudad::with(['departamento.pais', 'ubicaciones'])->get();

        return response()->json([
            'success' => true,
            'data' => $ciudades
        ], 200);
    }

    /**
     * Crear una nueva ciudad
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:120',
            'codigo_postal' => 'nullable|string|max:20',
            'id_departamento' => 'required|exists:departamento,id_departamento'
        ], [
            'nombre.required' => 'El nombre de la ciudad es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 120 caracteres',
            'codigo_postal.max' => 'El código postal no puede exceder 20 caracteres',
            'id_departamento.required' => 'El departamento es obligatorio',
            'id_departamento.exists' => 'El departamento seleccionado no existe'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $ciudad = Ciudad::create($request->all());
        $ciudad->load('departamento.pais');

        return response()->json([
            'success' => true,
            'message' => 'Ciudad creada exitosamente',
            'data' => $ciudad
        ], 201);
    }

    /**
     * Mostrar una ciudad específica
     */
    public function show(string $id)
    {
        $ciudad = Ciudad::with(['departamento.pais', 'ubicaciones'])->find($id);

        if (!$ciudad) {
            return response()->json([
                'success' => false,
                'message' => 'Ciudad no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $ciudad
        ], 200);
    }

    /**
     * Actualizar una ciudad
     */
    public function update(Request $request, string $id)
    {
        $ciudad = Ciudad::find($id);

        if (!$ciudad) {
            return response()->json([
                'success' => false,
                'message' => 'Ciudad no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:120',
            'codigo_postal' => 'nullable|string|max:20',
            'id_departamento' => 'required|exists:departamento,id_departamento'
        ], [
            'nombre.required' => 'El nombre de la ciudad es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 120 caracteres',
            'codigo_postal.max' => 'El código postal no puede exceder 20 caracteres',
            'id_departamento.required' => 'El departamento es obligatorio',
            'id_departamento.exists' => 'El departamento seleccionado no existe'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $ciudad->update($request->all());
        $ciudad->load('departamento.pais');

        return response()->json([
            'success' => true,
            'message' => 'Ciudad actualizada exitosamente',
            'data' => $ciudad
        ], 200);
    }

    /**
     * Eliminar una ciudad
     */
    public function destroy(string $id)
    {
        $ciudad = Ciudad::find($id);

        if (!$ciudad) {
            return response()->json([
                'success' => false,
                'message' => 'Ciudad no encontrada'
            ], 404);
        }

        // Verificar si tiene ubicaciones asociadas
        if ($ciudad->ubicaciones()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la ciudad porque tiene ubicaciones asociadas'
            ], 409);
        }

        $ciudad->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ciudad eliminada exitosamente'
        ], 200);
    }

    /**
     * Listar ciudades por departamento
     */
    public function byDepartamento(string $id_departamento)
    {
        $ciudades = Ciudad::where('id_departamento', $id_departamento)
            ->with('ubicaciones')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $ciudades
        ], 200);
    }

    /**
     * Buscar ciudades por código postal
     */
    public function byCodigoPostal(string $codigo_postal)
    {
        $ciudades = Ciudad::where('codigo_postal', $codigo_postal)
            ->with(['departamento.pais'])
            ->get();

        if ($ciudades->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron ciudades con ese código postal'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $ciudades
        ], 200);
    }
}