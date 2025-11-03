<?php

namespace App\Http\Controllers;

use App\Models\Ubicacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UbicacionController extends Controller
{
    /**
     * Listar todas las ubicaciones
     */
    public function index()
    {
        $ubicaciones = Ubicacion::with(['ciudad.departamento.pais', 'proyectos'])->get();

        return response()->json([
            'success' => true,
            'data' => $ubicaciones
        ], 200);
    }

    /**
     * Crear una nueva ubicación
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_ciudad' => 'required|exists:ciudad,id_ciudad',
            'barrio' => 'nullable|string|max:120',
            'direccion' => 'required|string|max:300'
        ], [
            'id_ciudad.required' => 'La ciudad es obligatoria',
            'id_ciudad.exists' => 'La ciudad seleccionada no existe',
            'barrio.max' => 'El barrio no puede exceder 120 caracteres',
            'direccion.required' => 'La dirección es obligatoria',
            'direccion.max' => 'La dirección no puede exceder 300 caracteres'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $ubicacion = Ubicacion::create($request->all());
        $ubicacion->load('ciudad.departamento.pais');

        return response()->json([
            'success' => true,
            'message' => 'Ubicación creada exitosamente',
            'data' => $ubicacion
        ], 201);
    }

    /**
     * Mostrar una ubicación específica
     */
    public function show(string $id)
    {
        $ubicacion = Ubicacion::with(['ciudad.departamento.pais', 'proyectos'])->find($id);

        if (!$ubicacion) {
            return response()->json([
                'success' => false,
                'message' => 'Ubicación no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $ubicacion
        ], 200);
    }

    /**
     * Actualizar una ubicación
     */
    public function update(Request $request, string $id)
    {
        $ubicacion = Ubicacion::find($id);

        if (!$ubicacion) {
            return response()->json([
                'success' => false,
                'message' => 'Ubicación no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'id_ciudad' => 'required|exists:ciudad,id_ciudad',
            'barrio' => 'nullable|string|max:120',
            'direccion' => 'required|string|max:300'
        ], [
            'id_ciudad.required' => 'La ciudad es obligatoria',
            'id_ciudad.exists' => 'La ciudad seleccionada no existe',
            'barrio.max' => 'El barrio no puede exceder 120 caracteres',
            'direccion.required' => 'La dirección es obligatoria',
            'direccion.max' => 'La dirección no puede exceder 300 caracteres'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $ubicacion->update($request->all());
        $ubicacion->load('ciudad.departamento.pais');

        return response()->json([
            'success' => true,
            'message' => 'Ubicación actualizada exitosamente',
            'data' => $ubicacion
        ], 200);
    }

    /**
     * Eliminar una ubicación
     */
    public function destroy(string $id)
    {
        $ubicacion = Ubicacion::find($id);

        if (!$ubicacion) {
            return response()->json([
                'success' => false,
                'message' => 'Ubicación no encontrada'
            ], 404);
        }

        // Verificar si tiene proyectos asociados
        if ($ubicacion->proyectos()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la ubicación porque tiene proyectos asociados'
            ], 409);
        }

        $ubicacion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ubicación eliminada exitosamente'
        ], 200);
    }

    /**
     * Listar ubicaciones por ciudad
     */
    public function byCiudad(string $id_ciudad)
    {
        $ubicaciones = Ubicacion::where('id_ciudad', $id_ciudad)
            ->with('proyectos')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $ubicaciones
        ], 200);
    }

    /**
     * Listar ubicaciones por barrio
     */
    public function byBarrio(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'barrio' => 'required|string'
        ], [
            'barrio.required' => 'El nombre del barrio es obligatorio'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $ubicaciones = Ubicacion::where('barrio', 'ILIKE', '%' . $request->barrio . '%')
            ->with(['ciudad.departamento.pais', 'proyectos'])
            ->get();

        if ($ubicaciones->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron ubicaciones en ese barrio'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $ubicaciones
        ], 200);
    }

    /**
     * Buscar ubicaciones por dirección
     */
    public function buscarPorDireccion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'direccion' => 'required|string'
        ], [
            'direccion.required' => 'La dirección es obligatoria para la búsqueda'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $ubicaciones = Ubicacion::where('direccion', 'ILIKE', '%' . $request->direccion . '%')
            ->with(['ciudad.departamento.pais', 'proyectos'])
            ->get();

        if ($ubicaciones->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron ubicaciones con esa dirección'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $ubicaciones
        ], 200);
    }

    /**
     * Obtener ubicación completa con jerarquía geográfica
     */
    public function ubicacionCompleta(string $id)
    {
        $ubicacion = Ubicacion::with('ciudad.departamento.pais')->find($id);

        if (!$ubicacion) {
            return response()->json([
                'success' => false,
                'message' => 'Ubicación no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id_ubicacion' => $ubicacion->id_ubicacion,
                'direccion' => $ubicacion->direccion,
                'barrio' => $ubicacion->barrio,
                'ciudad' => $ubicacion->ciudad->nombre,
                'departamento' => $ubicacion->ciudad->departamento->nombre,
                'pais' => $ubicacion->ciudad->departamento->pais->nombre,
                'direccion_completa' => $ubicacion->direccion . 
                    ($ubicacion->barrio ? ', ' . $ubicacion->barrio : '') . 
                    ', ' . $ubicacion->ciudad->nombre . 
                    ', ' . $ubicacion->ciudad->departamento->nombre . 
                    ', ' . $ubicacion->ciudad->departamento->pais->nombre
            ]
        ], 200);
    }
}