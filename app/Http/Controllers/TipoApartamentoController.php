<?php

namespace App\Http\Controllers;

use App\Models\TipoApartamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TipoApartamentoController extends Controller
{
    /**
     * Listar todos los tipos de apartamento
     */
    public function index()
    {
        $tiposApartamento = TipoApartamento::with('apartamentos')->get();

        return response()->json([
            'success' => true,
            'data' => $tiposApartamento
        ], 200);
    }

    /**
     * Crear un nuevo tipo de apartamento
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'area_construida' => 'nullable|numeric|min:0|max:99999999.99',
            'area_privada' => 'nullable|numeric|min:0|max:99999999.99',
            'cantidad_habitaciones' => 'nullable|integer|min:0|max:32767',
            'cantidad_banos' => 'nullable|integer|min:0|max:32767',
            'valor_m2' => 'nullable|numeric|min:0|max:9999999999999999.99'
        ], [
            'nombre.required' => 'El nombre del tipo de apartamento es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres',
            'area_construida.numeric' => 'El área construida debe ser un valor numérico',
            'area_construida.min' => 'El área construida no puede ser negativa',
            'area_privada.numeric' => 'El área privada debe ser un valor numérico',
            'area_privada.min' => 'El área privada no puede ser negativa',
            'cantidad_habitaciones.integer' => 'La cantidad de habitaciones debe ser un número entero',
            'cantidad_habitaciones.min' => 'La cantidad de habitaciones no puede ser negativa',
            'cantidad_banos.integer' => 'La cantidad de baños debe ser un número entero',
            'cantidad_banos.min' => 'La cantidad de baños no puede ser negativa',
            'valor_m2.numeric' => 'El valor por m² debe ser un valor numérico',
            'valor_m2.min' => 'El valor por m² no puede ser negativo'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $tipoApartamento = TipoApartamento::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Tipo de apartamento creado exitosamente',
            'data' => $tipoApartamento
        ], 201);
    }

    /**
     * Mostrar un tipo de apartamento específico
     */
    public function show(string $id)
    {
        $tipoApartamento = TipoApartamento::with(['apartamentos.torre', 'apartamentos.estadoInmueble'])->find($id);

        if (!$tipoApartamento) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de apartamento no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $tipoApartamento
        ], 200);
    }

    /**
     * Actualizar un tipo de apartamento
     */
    public function update(Request $request, string $id)
    {
        $tipoApartamento = TipoApartamento::find($id);

        if (!$tipoApartamento) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de apartamento no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'area_construida' => 'nullable|numeric|min:0|max:99999999.99',
            'area_privada' => 'nullable|numeric|min:0|max:99999999.99',
            'cantidad_habitaciones' => 'nullable|integer|min:0|max:32767',
            'cantidad_banos' => 'nullable|integer|min:0|max:32767',
            'valor_m2' => 'nullable|numeric|min:0|max:9999999999999999.99'
        ], [
            'nombre.required' => 'El nombre del tipo de apartamento es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres',
            'area_construida.numeric' => 'El área construida debe ser un valor numérico',
            'area_construida.min' => 'El área construida no puede ser negativa',
            'area_privada.numeric' => 'El área privada debe ser un valor numérico',
            'area_privada.min' => 'El área privada no puede ser negativa',
            'cantidad_habitaciones.integer' => 'La cantidad de habitaciones debe ser un número entero',
            'cantidad_habitaciones.min' => 'La cantidad de habitaciones no puede ser negativa',
            'cantidad_banos.integer' => 'La cantidad de baños debe ser un número entero',
            'cantidad_banos.min' => 'La cantidad de baños no puede ser negativa',
            'valor_m2.numeric' => 'El valor por m² debe ser un valor numérico',
            'valor_m2.min' => 'El valor por m² no puede ser negativo'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $tipoApartamento->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Tipo de apartamento actualizado exitosamente',
            'data' => $tipoApartamento
        ], 200);
    }

    /**
     * Eliminar un tipo de apartamento
     */
    public function destroy(string $id)
    {
        $tipoApartamento = TipoApartamento::find($id);

        if (!$tipoApartamento) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de apartamento no encontrado'
            ], 404);
        }

        // Verificar si tiene apartamentos asociados
        if ($tipoApartamento->apartamentos()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el tipo de apartamento porque tiene apartamentos asociados'
            ], 409);
        }

        $tipoApartamento->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tipo de apartamento eliminado exitosamente'
        ], 200);
    }

    /**
     * Listar tipos de apartamento con conteo
     */
    public function withCount()
    {
        $tiposApartamento = TipoApartamento::withCount('apartamentos')->get();

        return response()->json([
            'success' => true,
            'data' => $tiposApartamento
        ], 200);
    }

    /**
     * Calcular valor estimado del apartamento
     */
    public function calcularValor(string $id)
    {
        $tipoApartamento = TipoApartamento::find($id);

        if (!$tipoApartamento) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de apartamento no encontrado'
            ], 404);
        }

        $valorEstimado = null;
        if ($tipoApartamento->area_construida && $tipoApartamento->valor_m2) {
            $valorEstimado = $tipoApartamento->area_construida * $tipoApartamento->valor_m2;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id_tipo_apartamento' => $tipoApartamento->id_tipo_apartamento,
                'nombre' => $tipoApartamento->nombre,
                'area_construida' => $tipoApartamento->area_construida,
                'area_privada' => $tipoApartamento->area_privada,
                'valor_m2' => $tipoApartamento->valor_m2,
                'valor_estimado' => $valorEstimado
            ]
        ], 200);
    }

    /**
     * Filtrar tipos de apartamento por características
     */
    public function filtrar(Request $request)
    {
        $query = TipoApartamento::query();

        if ($request->has('habitaciones_min')) {
            $query->where('cantidad_habitaciones', '>=', $request->habitaciones_min);
        }

        if ($request->has('habitaciones_max')) {
            $query->where('cantidad_habitaciones', '<=', $request->habitaciones_max);
        }

        if ($request->has('banos_min')) {
            $query->where('cantidad_banos', '>=', $request->banos_min);
        }

        if ($request->has('area_min')) {
            $query->where('area_construida', '>=', $request->area_min);
        }

        if ($request->has('area_max')) {
            $query->where('area_construida', '<=', $request->area_max);
        }

        if ($request->has('valor_m2_max')) {
            $query->where('valor_m2', '<=', $request->valor_m2_max);
        }

        $tiposApartamento = $query->with('apartamentos')->get();

        return response()->json([
            'success' => true,
            'data' => $tiposApartamento
        ], 200);
    }
}