<?php

namespace App\Http\Controllers;

use App\Models\EstadoInmueble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EstadoInmuebleController extends Controller
{
    /**
     * Listar todos los estados de inmueble
     */
    public function index()
    {
        $estadosInmueble = EstadoInmueble::with(['apartamentos', 'locales'])->get();

        return response()->json([
            'success' => true,
            'data' => $estadosInmueble
        ], 200);
    }

    /**
     * Crear un nuevo estado de inmueble
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:50|unique:estado_inmueble,nombre',
            'descripcion' => 'nullable|string|max:200'
        ], [
            'nombre.required' => 'El nombre del estado de inmueble es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 50 caracteres',
            'nombre.unique' => 'Este estado de inmueble ya existe en el sistema',
            'descripcion.max' => 'La descripción no puede exceder 200 caracteres'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $estadoInmueble = EstadoInmueble::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Estado de inmueble creado exitosamente',
            'data' => $estadoInmueble
        ], 201);
    }

    /**
     * Mostrar un estado de inmueble específico
     */
    public function show(string $id)
    {
        $estadoInmueble = EstadoInmueble::with(['apartamentos', 'locales'])->find($id);

        if (!$estadoInmueble) {
            return response()->json([
                'success' => false,
                'message' => 'Estado de inmueble no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $estadoInmueble
        ], 200);
    }

    /**
     * Actualizar un estado de inmueble
     */
    public function update(Request $request, string $id)
    {
        $estadoInmueble = EstadoInmueble::find($id);

        if (!$estadoInmueble) {
            return response()->json([
                'success' => false,
                'message' => 'Estado de inmueble no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:50|unique:estado_inmueble,nombre,' . $id . ',id_estado_inmueble',
            'descripcion' => 'nullable|string|max:200'
        ], [
            'nombre.required' => 'El nombre del estado de inmueble es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 50 caracteres',
            'nombre.unique' => 'Este estado de inmueble ya existe en el sistema',
            'descripcion.max' => 'La descripción no puede exceder 200 caracteres'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $estadoInmueble->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Estado de inmueble actualizado exitosamente',
            'data' => $estadoInmueble
        ], 200);
    }

    /**
     * Eliminar un estado de inmueble
     */
    public function destroy(string $id)
    {
        $estadoInmueble = EstadoInmueble::find($id);

        if (!$estadoInmueble) {
            return response()->json([
                'success' => false,
                'message' => 'Estado de inmueble no encontrado'
            ], 404);
        }

        // Verificar si tiene apartamentos o locales asociados
        $apartamentosCount = $estadoInmueble->apartamentos()->count();
        $localesCount = $estadoInmueble->locales()->count();

        if ($apartamentosCount > 0 || $localesCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el estado de inmueble porque tiene ' . 
                            ($apartamentosCount > 0 ? $apartamentosCount . ' apartamento(s)' : '') .
                            ($apartamentosCount > 0 && $localesCount > 0 ? ' y ' : '') .
                            ($localesCount > 0 ? $localesCount . ' local(es)' : '') . ' asociado(s)'
            ], 409);
        }

        $estadoInmueble->delete();

        return response()->json([
            'success' => true,
            'message' => 'Estado de inmueble eliminado exitosamente'
        ], 200);
    }

    /**
     * Listar estados de inmueble con conteo de apartamentos y locales
     */
    public function withCount()
    {
        $estadosInmueble = EstadoInmueble::withCount(['apartamentos', 'locales'])->get();

        return response()->json([
            'success' => true,
            'data' => $estadosInmueble
        ], 200);
    }

    /**
     * Obtener estadísticas de inmuebles por estado
     */
    public function estadisticas()
    {
        $estadosInmueble = EstadoInmueble::withCount(['apartamentos', 'locales'])
            ->get()
            ->map(function ($estado) {
                return [
                    'id_estado_inmueble' => $estado->id_estado_inmueble,
                    'nombre' => $estado->nombre,
                    'descripcion' => $estado->descripcion,
                    'total_apartamentos' => $estado->apartamentos_count,
                    'total_locales' => $estado->locales_count,
                    'total_inmuebles' => $estado->apartamentos_count + $estado->locales_count
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $estadosInmueble
        ], 200);
    }
}