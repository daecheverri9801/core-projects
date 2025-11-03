<?php

namespace App\Http\Controllers;

use App\Models\PisoTorre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PisoTorreController extends Controller
{
    /**
     * Listar todos los pisos
     */
    public function index()
    {
        $pisos = PisoTorre::with(['torre.proyecto', 'apartamentos'])->get();

        return response()->json([
            'success' => true,
            'data' => $pisos
        ], 200);
    }

    /**
     * Crear un nuevo piso
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nivel' => 'required|integer',
            'id_torre' => 'required|exists:torre,id_torre',
            'uso' => 'nullable|string|max:40'
        ], [
            'nivel.required' => 'El nivel del piso es obligatorio',
            'nivel.integer' => 'El nivel debe ser un número entero',
            'id_torre.required' => 'La torre es obligatoria',
            'id_torre.exists' => 'La torre seleccionada no existe',
            'uso.max' => 'El uso no puede exceder 40 caracteres'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que no exista un piso con el mismo nivel en la misma torre
        $pisoExistente = PisoTorre::where('id_torre', $request->id_torre)
            ->where('nivel', $request->nivel)
            ->first();

        if ($pisoExistente) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe un piso con este nivel en la torre seleccionada'
            ], 409);
        }

        $piso = PisoTorre::create($request->all());
        $piso->load(['torre.proyecto']);

        return response()->json([
            'success' => true,
            'message' => 'Piso creado exitosamente',
            'data' => $piso
        ], 201);
    }

    /**
     * Mostrar un piso específico
     */
    public function show(string $id)
    {
        $piso = PisoTorre::with([
            'torre.proyecto',
            'apartamentos.tipoApartamento',
            'apartamentos.estadoInmueble'
        ])->find($id);

        if (!$piso) {
            return response()->json([
                'success' => false,
                'message' => 'Piso no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $piso
        ], 200);
    }

    /**
     * Actualizar un piso
     */
    public function update(Request $request, string $id)
    {
        $piso = PisoTorre::find($id);

        if (!$piso) {
            return response()->json([
                'success' => false,
                'message' => 'Piso no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nivel' => 'required|integer',
            'id_torre' => 'required|exists:torre,id_torre',
            'uso' => 'nullable|string|max:40'
        ], [
            'nivel.required' => 'El nivel del piso es obligatorio',
            'nivel.integer' => 'El nivel debe ser un número entero',
            'id_torre.required' => 'La torre es obligatoria',
            'id_torre.exists' => 'La torre seleccionada no existe',
            'uso.max' => 'El uso no puede exceder 40 caracteres'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que no exista otro piso con el mismo nivel en la misma torre
        $pisoExistente = PisoTorre::where('id_torre', $request->id_torre)
            ->where('nivel', $request->nivel)
            ->where('id_piso_torre', '!=', $id)
            ->first();

        if ($pisoExistente) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe otro piso con este nivel en la torre seleccionada'
            ], 409);
        }

        $piso->update($request->all());
        $piso->load(['torre.proyecto']);

        return response()->json([
            'success' => true,
            'message' => 'Piso actualizado exitosamente',
            'data' => $piso
        ], 200);
    }

    /**
     * Eliminar un piso
     */
    public function destroy(string $id)
    {
        $piso = PisoTorre::find($id);

        if (!$piso) {
            return response()->json([
                'success' => false,
                'message' => 'Piso no encontrado'
            ], 404);
        }

        // Verificar si tiene apartamentos asociados
        if ($piso->apartamentos()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el piso porque tiene apartamentos asociados'
            ], 409);
        }

        $piso->delete();

        return response()->json([
            'success' => true,
            'message' => 'Piso eliminado exitosamente'
        ], 200);
    }

    /**
     * Listar pisos por torre
     */
    public function byTorre(string $id_torre)
    {
        $pisos = PisoTorre::where('id_torre', $id_torre)
            ->with('apartamentos')
            ->orderBy('nivel', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pisos
        ], 200);
    }

    /**
     * Listar pisos por uso
     */
    public function byUso(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uso' => 'required|string'
        ], [
            'uso.required' => 'El uso es obligatorio para la búsqueda'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $pisos = PisoTorre::where('uso', 'ILIKE', '%' . $request->uso . '%')
            ->with(['torre.proyecto', 'apartamentos'])
            ->get();

        if ($pisos->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron pisos con ese uso'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $pisos
        ], 200);
    }

    /**
     * Obtener resumen de un piso
     */
    public function resumen(string $id)
    {
        $piso = PisoTorre::with([
            'torre.proyecto',
            'apartamentos.estadoInmueble',
            'apartamentos.tipoApartamento'
        ])->find($id);

        if (!$piso) {
            return response()->json([
                'success' => false,
                'message' => 'Piso no encontrado'
            ], 404);
        }

        $totalApartamentos = $piso->apartamentos->count();
        
        $apartamentosPorEstado = $piso->apartamentos->groupBy('id_estado_inmueble')->map(function ($group) {
            return [
                'estado' => $group->first()->estadoInmueble->nombre ?? 'Sin estado',
                'cantidad' => $group->count()
            ];
        })->values();

        $apartamentosPorTipo = $piso->apartamentos->groupBy('id_tipo_apartamento')->map(function ($group) {
            return [
                'tipo' => $group->first()->tipoApartamento->nombre ?? 'Sin tipo',
                'cantidad' => $group->count()
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'id_piso_torre' => $piso->id_piso_torre,
                'nivel' => $piso->nivel,
                'uso' => $piso->uso,
                'torre' => $piso->torre->nombre_torre,
                'proyecto' => $piso->torre->proyecto->nombre,
                'total_apartamentos' => $totalApartamentos,
                'apartamentos_por_estado' => $apartamentosPorEstado,
                'apartamentos_por_tipo' => $apartamentosPorTipo
            ]
        ], 200);
    }

    /**
     * Crear múltiples pisos para una torre
     */
    public function crearMultiples(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_torre' => 'required|exists:torre,id_torre',
            'nivel_inicio' => 'required|integer',
            'nivel_fin' => 'required|integer|gte:nivel_inicio',
            'uso' => 'nullable|string|max:40'
        ], [
            'id_torre.required' => 'La torre es obligatoria',
            'id_torre.exists' => 'La torre seleccionada no existe',
            'nivel_inicio.required' => 'El nivel de inicio es obligatorio',
            'nivel_inicio.integer' => 'El nivel de inicio debe ser un número entero',
            'nivel_fin.required' => 'El nivel final es obligatorio',
            'nivel_fin.integer' => 'El nivel final debe ser un número entero',
            'nivel_fin.gte' => 'El nivel final debe ser mayor o igual al nivel de inicio',
            'uso.max' => 'El uso no puede exceder 40 caracteres'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $pisosCreados = [];
        $pisosExistentes = [];

        for ($nivel = $request->nivel_inicio; $nivel <= $request->nivel_fin; $nivel++) {
            // Verificar si ya existe el piso
            $pisoExistente = PisoTorre::where('id_torre', $request->id_torre)
                ->where('nivel', $nivel)
                ->first();

            if ($pisoExistente) {
                $pisosExistentes[] = $nivel;
                continue;
            }

            $piso = PisoTorre::create([
                'nivel' => $nivel,
                'id_torre' => $request->id_torre,
                'uso' => $request->uso
            ]);

            $pisosCreados[] = $piso;
        }

        return response()->json([
            'success' => true,
            'message' => count($pisosCreados) . ' piso(s) creado(s) exitosamente',
            'data' => [
                'pisos_creados' => $pisosCreados,
                'niveles_existentes' => $pisosExistentes
            ]
        ], 201);
    }

    /**
     * Obtener estadísticas de pisos por torre
     */
    public function estadisticasPorTorre(string $id_torre)
    {
        $pisos = PisoTorre::where('id_torre', $id_torre)
            ->withCount('apartamentos')
            ->orderBy('nivel', 'asc')
            ->get();

        if ($pisos->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron pisos para esta torre'
            ], 404);
        }

        $estadisticas = $pisos->map(function ($piso) {
            return [
                'id_piso_torre' => $piso->id_piso_torre,
                'nivel' => $piso->nivel,
                'uso' => $piso->uso,
                'total_apartamentos' => $piso->apartamentos_count
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'total_pisos' => $pisos->count(),
                'pisos' => $estadisticas
            ]
        ], 200);
    }
}