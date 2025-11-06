<?php

namespace App\Http\Controllers;

use App\Models\Torre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TorreController extends Controller
{
    /**
     * Listar todas las torres
     */
    public function index()
    {
        $torres = Torre::with(['proyecto', 'estado', 'pisos', 'apartamentos'])->get();

        return response()->json([
            'success' => true,
            'data' => $torres
        ], 200);
    }

    /**
     * Crear una nueva torre
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_torre' => 'required|string|max:50',
            'numero_pisos' => 'nullable|integer|min:1|max:32767',
            'id_proyecto' => 'required|exists:proyectos,id_proyecto',
            'id_estado' => 'required|exists:estados,id_estado'
        ], [
            'nombre_torre.required' => 'El nombre de la torre es obligatorio',
            'nombre_torre.max' => 'El nombre de la torre no puede exceder 50 caracteres',
            'numero_pisos.integer' => 'El número de pisos debe ser un número entero',
            'numero_pisos.min' => 'El número de pisos debe ser al menos 1',
            'id_proyecto.required' => 'El proyecto es obligatorio',
            'id_proyecto.exists' => 'El proyecto seleccionado no existe',
            'id_estado.required' => 'El estado es obligatorio',
            'id_estado.exists' => 'El estado seleccionado no existe'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $torre = Torre::create($request->all());
        $torre->load(['proyecto', 'estado']);

        return response()->json([
            'success' => true,
            'message' => 'Torre creada exitosamente',
            'data' => $torre
        ], 201);
    }

    /**
     * Mostrar una torre específica
     */
    public function show(string $id)
    {
        $torre = Torre::with([
            'proyecto.ubicacion.ciudad',
            'estado',
            'pisos',
            'apartamentos.tipoApartamento',
            'apartamentos.estadoInmueble'
        ])->find($id);

        if (!$torre) {
            return response()->json([
                'success' => false,
                'message' => 'Torre no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $torre
        ], 200);
    }

    /**
     * Actualizar una torre
     */
    public function update(Request $request, string $id)
    {
        $torre = Torre::find($id);

        if (!$torre) {
            return response()->json([
                'success' => false,
                'message' => 'Torre no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre_torre' => 'required|string|max:50',
            'numero_pisos' => 'nullable|integer|min:1|max:32767',
            'id_proyecto' => 'required|exists:proyectos,id_proyecto',
            'id_estado' => 'required|exists:estados,id_estado'
        ], [
            'nombre_torre.required' => 'El nombre de la torre es obligatorio',
            'nombre_torre.max' => 'El nombre de la torre no puede exceder 50 caracteres',
            'numero_pisos.integer' => 'El número de pisos debe ser un número entero',
            'numero_pisos.min' => 'El número de pisos debe ser al menos 1',
            'id_proyecto.required' => 'El proyecto es obligatorio',
            'id_proyecto.exists' => 'El proyecto seleccionado no existe',
            'id_estado.required' => 'El estado es obligatorio',
            'id_estado.exists' => 'El estado seleccionado no existe'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $torre->update($request->all());
        $torre->load(['proyecto', 'estado']);

        return response()->json([
            'success' => true,
            'message' => 'Torre actualizada exitosamente',
            'data' => $torre
        ], 200);
    }

    /**
     * Eliminar una torre
     */
    public function destroy(string $id)
    {
        $torre = Torre::find($id);

        if (!$torre) {
            return response()->json([
                'success' => false,
                'message' => 'Torre no encontrada'
            ], 404);
        }

        // Verificar si tiene pisos o apartamentos asociados
        $pisosCount = $torre->pisos()->count();
        $apartamentosCount = $torre->apartamentos()->count();

        if ($pisosCount > 0 || $apartamentosCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la torre porque tiene ' . 
                            ($pisosCount > 0 ? $pisosCount . ' piso(s)' : '') .
                            ($pisosCount > 0 && $apartamentosCount > 0 ? ' y ' : '') .
                            ($apartamentosCount > 0 ? $apartamentosCount . ' apartamento(s)' : '') . ' asociado(s)'
            ], 409);
        }

        $torre->delete();

        return response()->json([
            'success' => true,
            'message' => 'Torre eliminada exitosamente'
        ], 200);
    }

    /**
     * Listar torres por proyecto
     */
    public function byProyecto(string $id_proyecto)
    {
        $torres = Torre::where('id_proyecto', $id_proyecto)
            ->with(['estado', 'pisos', 'apartamentos'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $torres
        ], 200);
    }

    /**
     * Listar torres por estado
     */
    public function byEstado(string $id_estado)
    {
        $torres = Torre::where('id_estado', $id_estado)
            ->with(['proyecto', 'estado'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $torres
        ], 200);
    }

    /**
     * Obtener resumen de una torre
     */
    public function resumen(string $id)
    {
        $torre = Torre::with([
            'proyecto',
            'estado',
            'pisos',
            'apartamentos.estadoInmueble'
        ])->find($id);

        if (!$torre) {
            return response()->json([
                'success' => false,
                'message' => 'Torre no encontrada'
            ], 404);
        }

        $totalPisos = $torre->pisos->count();
        $totalApartamentos = $torre->apartamentos->count();
        
        $apartamentosPorEstado = $torre->apartamentos->groupBy('id_estado_inmueble')->map(function ($group) {
            return [
                'estado' => $group->first()->estadoInmueble->nombre ?? 'Sin estado',
                'cantidad' => $group->count()
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'id_torre' => $torre->id_torre,
                'nombre_torre' => $torre->nombre_torre,
                'numero_pisos' => $torre->numero_pisos,
                'proyecto' => $torre->proyecto->nombre,
                'estado' => $torre->estado->nombre,
                'pisos_registrados' => $totalPisos,
                'total_apartamentos' => $totalApartamentos,
                'apartamentos_por_estado' => $apartamentosPorEstado
            ]
        ], 200);
    }

    /**
     * Obtener estadísticas de torres por proyecto
     */
    public function estadisticasPorProyecto(string $id_proyecto)
    {
        $torres = Torre::where('id_proyecto', $id_proyecto)
            ->withCount(['pisos', 'apartamentos'])
            ->with('estado')
            ->get();

        if ($torres->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron torres para este proyecto'
            ], 404);
        }

        $estadisticas = $torres->map(function ($torre) {
            return [
                'id_torre' => $torre->id_torre,
                'nombre_torre' => $torre->nombre_torre,
                'numero_pisos' => $torre->numero_pisos,
                'estado' => $torre->estado->nombre,
                'pisos_registrados' => $torre->pisos_count,
                'total_apartamentos' => $torre->apartamentos_count
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'total_torres' => $torres->count(),
                'torres' => $estadisticas
            ]
        ], 200);
    }

    /**
     * Buscar torres por nombre
     */
    public function buscar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'termino' => 'required|string|min:1'
        ], [
            'termino.required' => 'El término de búsqueda es obligatorio',
            'termino.min' => 'El término de búsqueda debe tener al menos 1 caracter'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $torres = Torre::where('nombre_torre', 'ILIKE', '%' . $request->termino . '%')
            ->with(['proyecto', 'estado'])
            ->get();

        if ($torres->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron torres con ese término de búsqueda'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $torres
        ], 200);
    }
}