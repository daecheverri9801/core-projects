<?php

namespace App\Http\Controllers;

use App\Models\Dependencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DependenciaController extends Controller
{
    /**
     * Listar todas las dependencias
     */
    public function index()
    {
        $dependencias = Dependencia::with('empleados')->get();

        return response()->json([
            'success' => true,
            'data' => $dependencias
        ], 200);
    }

    /**
     * Crear una nueva dependencia
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:80|unique:dependencia,nombre',
            'descripcion' => 'nullable|string|max:200'
        ], [
            'nombre.required' => 'El nombre de la dependencia es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 80 caracteres',
            'nombre.unique' => 'Esta dependencia ya existe en el sistema',
            'descripcion.max' => 'La descripción no puede exceder 200 caracteres'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $dependencia = Dependencia::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Dependencia creada exitosamente',
            'data' => $dependencia
        ], 201);
    }

    /**
     * Mostrar una dependencia específica
     */
    public function show(string $id)
    {
        $dependencia = Dependencia::with('empleados.cargo')->find($id);

        if (!$dependencia) {
            return response()->json([
                'success' => false,
                'message' => 'Dependencia no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $dependencia
        ], 200);
    }

    /**
     * Actualizar una dependencia
     */
    public function update(Request $request, string $id)
    {
        $dependencia = Dependencia::find($id);

        if (!$dependencia) {
            return response()->json([
                'success' => false,
                'message' => 'Dependencia no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:80|unique:dependencia,nombre,' . $id . ',id_dependencia',
            'descripcion' => 'nullable|string|max:200'
        ], [
            'nombre.required' => 'El nombre de la dependencia es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 80 caracteres',
            'nombre.unique' => 'Esta dependencia ya existe en el sistema',
            'descripcion.max' => 'La descripción no puede exceder 200 caracteres'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $dependencia->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Dependencia actualizada exitosamente',
            'data' => $dependencia
        ], 200);
    }

    /**
     * Eliminar una dependencia
     */
    public function destroy(string $id)
    {
        $dependencia = Dependencia::find($id);

        if (!$dependencia) {
            return response()->json([
                'success' => false,
                'message' => 'Dependencia no encontrada'
            ], 404);
        }

        // Verificar si tiene empleados asociados
        if ($dependencia->empleados()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la dependencia porque tiene empleados asociados'
            ], 409);
        }

        $dependencia->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dependencia eliminada exitosamente'
        ], 200);
    }

    /**
     * Listar dependencias con conteo de empleados
     */
    public function withCount()
    {
        $dependencias = Dependencia::withCount('empleados')->get();

        return response()->json([
            'success' => true,
            'data' => $dependencias
        ], 200);
    }

    /**
     * Listar empleados por dependencia
     */
    public function empleados(string $id)
    {
        $dependencia = Dependencia::with(['empleados' => function ($query) {
            $query->where('estado', true);
        }, 'empleados.cargo'])->find($id);

        if (!$dependencia) {
            return response()->json([
                'success' => false,
                'message' => 'Dependencia no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'dependencia' => $dependencia->nombre,
                'descripcion' => $dependencia->descripcion,
                'empleados' => $dependencia->empleados
            ]
        ], 200);
    }

    /**
     * Obtener estadísticas de la dependencia
     */
    public function estadisticas(string $id)
    {
        $dependencia = Dependencia::withCount([
            'empleados',
            'empleados as empleados_activos_count' => function ($query) {
                $query->where('estado', true);
            },
            'empleados as empleados_inactivos_count' => function ($query) {
                $query->where('estado', false);
            }
        ])->find($id);

        if (!$dependencia) {
            return response()->json([
                'success' => false,
                'message' => 'Dependencia no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id_dependencia' => $dependencia->id_dependencia,
                'nombre' => $dependencia->nombre,
                'descripcion' => $dependencia->descripcion,
                'total_empleados' => $dependencia->empleados_count,
                'empleados_activos' => $dependencia->empleados_activos_count,
                'empleados_inactivos' => $dependencia->empleados_inactivos_count
            ]
        ], 200);
    }
}