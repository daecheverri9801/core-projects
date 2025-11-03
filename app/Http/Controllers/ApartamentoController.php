<?php

namespace App\Http\Controllers;

use App\Models\Apartamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApartamentoController extends Controller
{
    /**
     * Listar todos los apartamentos
     */
    public function index()
    {
        $apartamentos = Apartamento::with([
            'tipoApartamento',
            'torre.proyecto',
            'pisoTorre',
            'estadoInmueble',
            'parqueaderos'
        ])->get();

        return response()->json([
            'success' => true,
            'data' => $apartamentos
        ], 200);
    }

    /**
     * Crear un nuevo apartamento
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'numero' => 'required|string|max:20',
            'id_tipo_apartamento' => 'required|exists:tipo_apartamento,id_tipo_apartamento',
            'id_torre' => 'required|exists:torre,id_torre',
            'id_piso_torre' => 'required|exists:piso_torre,id_piso_torre',
            'id_estado_inmueble' => 'required|exists:estado_inmueble,id_estado_inmueble',
            'valor_total' => 'nullable|numeric|min:0|max:9999999999999999.99'
        ], [
            'numero.required' => 'El número del apartamento es obligatorio',
            'numero.max' => 'El número del apartamento no puede exceder 20 caracteres',
            'id_tipo_apartamento.required' => 'El tipo de apartamento es obligatorio',
            'id_tipo_apartamento.exists' => 'El tipo de apartamento seleccionado no existe',
            'id_torre.required' => 'La torre es obligatoria',
            'id_torre.exists' => 'La torre seleccionada no existe',
            'id_piso_torre.required' => 'El piso es obligatorio',
            'id_piso_torre.exists' => 'El piso seleccionado no existe',
            'id_estado_inmueble.required' => 'El estado del inmueble es obligatorio',
            'id_estado_inmueble.exists' => 'El estado del inmueble seleccionado no existe',
            'valor_total.numeric' => 'El valor total debe ser un número',
            'valor_total.min' => 'El valor total no puede ser negativo'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que el piso pertenezca a la torre
        $piso = \App\Models\PisoTorre::find($request->id_piso_torre);
        if ($piso && $piso->id_torre != $request->id_torre) {
            return response()->json([
                'success' => false,
                'message' => 'El piso seleccionado no pertenece a la torre indicada'
            ], 422);
        }

        // Verificar que no exista un apartamento con el mismo número en la misma torre
        $apartamentoExistente = Apartamento::where('numero', $request->numero)
            ->where('id_torre', $request->id_torre)
            ->first();

        if ($apartamentoExistente) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe un apartamento con este número en la torre seleccionada'
            ], 409);
        }

        $apartamento = Apartamento::create($request->all());
        $apartamento->load([
            'tipoApartamento',
            'torre.proyecto',
            'pisoTorre',
            'estadoInmueble'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Apartamento creado exitosamente',
            'data' => $apartamento
        ], 201);
    }

    /**
     * Mostrar un apartamento específico
     */
    public function show(string $id)
    {
        $apartamento = Apartamento::with([
            'tipoApartamento',
            'torre.proyecto.ubicacion.ciudad',
            'pisoTorre',
            'estadoInmueble',
            'parqueaderos'
        ])->find($id);

        if (!$apartamento) {
            return response()->json([
                'success' => false,
                'message' => 'Apartamento no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $apartamento
        ], 200);
    }

    /**
     * Actualizar un apartamento
     */
    public function update(Request $request, string $id)
    {
        $apartamento = Apartamento::find($id);

        if (!$apartamento) {
            return response()->json([
                'success' => false,
                'message' => 'Apartamento no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'numero' => 'required|string|max:20',
            'id_tipo_apartamento' => 'required|exists:tipo_apartamento,id_tipo_apartamento',
            'id_torre' => 'required|exists:torre,id_torre',
            'id_piso_torre' => 'required|exists:piso_torre,id_piso_torre',
            'id_estado_inmueble' => 'required|exists:estado_inmueble,id_estado_inmueble',
            'valor_total' => 'nullable|numeric|min:0|max:9999999999999999.99'
        ], [
            'numero.required' => 'El número del apartamento es obligatorio',
            'numero.max' => 'El número del apartamento no puede exceder 20 caracteres',
            'id_tipo_apartamento.required' => 'El tipo de apartamento es obligatorio',
            'id_tipo_apartamento.exists' => 'El tipo de apartamento seleccionado no existe',
            'id_torre.required' => 'La torre es obligatoria',
            'id_torre.exists' => 'La torre seleccionada no existe',
            'id_piso_torre.required' => 'El piso es obligatorio',
            'id_piso_torre.exists' => 'El piso seleccionado no existe',
            'id_estado_inmueble.required' => 'El estado del inmueble es obligatorio',
            'id_estado_inmueble.exists' => 'El estado del inmueble seleccionado no existe',
            'valor_total.numeric' => 'El valor total debe ser un número',
            'valor_total.min' => 'El valor total no puede ser negativo'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que el piso pertenezca a la torre
        $piso = \App\Models\PisoTorre::find($request->id_piso_torre);
        if ($piso && $piso->id_torre != $request->id_torre) {
            return response()->json([
                'success' => false,
                'message' => 'El piso seleccionado no pertenece a la torre indicada'
            ], 422);
        }

        // Verificar que no exista otro apartamento con el mismo número en la misma torre
        $apartamentoExistente = Apartamento::where('numero', $request->numero)
            ->where('id_torre', $request->id_torre)
            ->where('id_apartamento', '!=', $id)
            ->first();

        if ($apartamentoExistente) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe otro apartamento con este número en la torre seleccionada'
            ], 409);
        }

        $apartamento->update($request->all());
        $apartamento->load([
            'tipoApartamento',
            'torre.proyecto',
            'pisoTorre',
            'estadoInmueble'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Apartamento actualizado exitosamente',
            'data' => $apartamento
        ], 200);
    }

    /**
     * Eliminar un apartamento
     */
    public function destroy(string $id)
    {
        $apartamento = Apartamento::find($id);

        if (!$apartamento) {
            return response()->json([
                'success' => false,
                'message' => 'Apartamento no encontrado'
            ], 404);
        }

        // Verificar si tiene parqueaderos asociados
        if ($apartamento->parqueaderos()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el apartamento porque tiene parqueaderos asociados'
            ], 409);
        }

        $apartamento->delete();

        return response()->json([
            'success' => true,
            'message' => 'Apartamento eliminado exitosamente'
        ], 200);
    }

    /**
     * Listar apartamentos por torre
     */
    public function byTorre(string $id_torre)
    {
        $apartamentos = Apartamento::where('id_torre', $id_torre)
            ->with([
                'tipoApartamento',
                'pisoTorre',
                'estadoInmueble',
                'parqueaderos'
            ])
            ->orderBy('id_piso_torre')
            ->orderBy('numero')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $apartamentos
        ], 200);
    }

    /**
     * Listar apartamentos por piso
     */
    public function byPiso(string $id_piso_torre)
    {
        $apartamentos = Apartamento::where('id_piso_torre', $id_piso_torre)
            ->with([
                'tipoApartamento',
                'torre',
                'estadoInmueble',
                'parqueaderos'
            ])
            ->orderBy('numero')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $apartamentos
        ], 200);
    }

    /**
     * Listar apartamentos por estado
     */
    public function byEstado(string $id_estado_inmueble)
    {
        $apartamentos = Apartamento::where('id_estado_inmueble', $id_estado_inmueble)
            ->with([
                'tipoApartamento',
                'torre.proyecto',
                'pisoTorre',
                'estadoInmueble'
            ])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $apartamentos
        ], 200);
    }

    /**
     * Listar apartamentos por tipo
     */
    public function byTipo(string $id_tipo_apartamento)
    {
        $apartamentos = Apartamento::where('id_tipo_apartamento', $id_tipo_apartamento)
            ->with([
                'tipoApartamento',
                'torre.proyecto',
                'pisoTorre',
                'estadoInmueble'
            ])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $apartamentos
        ], 200);
    }

    /**
     * Listar apartamentos por proyecto
     */
    public function byProyecto(string $id_proyecto)
    {
        $apartamentos = Apartamento::whereHas('torre', function ($query) use ($id_proyecto) {
            $query->where('id_proyecto', $id_proyecto);
        })
        ->with([
            'tipoApartamento',
            'torre',
            'pisoTorre',
            'estadoInmueble'
        ])
        ->get();

        return response()->json([
            'success' => true,
            'data' => $apartamentos
        ], 200);
    }

    /**
     * Buscar apartamentos por número
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

        $apartamentos = Apartamento::where('numero', 'ILIKE', '%' . $request->termino . '%')
            ->with([
                'tipoApartamento',
                'torre.proyecto',
                'pisoTorre',
                'estadoInmueble'
            ])
            ->get();

        if ($apartamentos->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron apartamentos con ese término de búsqueda'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $apartamentos
        ], 200);
    }

    /**
     * Obtener resumen de un apartamento
     */
    public function resumen(string $id)
    {
        $apartamento = Apartamento::with([
            'tipoApartamento',
            'torre.proyecto.ubicacion.ciudad',
            'pisoTorre',
            'estadoInmueble',
            'parqueaderos'
        ])->find($id);

        if (!$apartamento) {
            return response()->json([
                'success' => false,
                'message' => 'Apartamento no encontrado'
            ], 404);
        }

        $totalParqueaderos = $apartamento->parqueaderos->count();
        $parqueaderosVehiculo = $apartamento->parqueaderos->where('tipo', 'Vehiculo')->count();
        $parqueaderosMoto = $apartamento->parqueaderos->where('tipo', 'Moto')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'id_apartamento' => $apartamento->id_apartamento,
                'numero' => $apartamento->numero,
                'tipo' => $apartamento->tipoApartamento->nombre,
                'torre' => $apartamento->torre->nombre_torre,
                'piso' => $apartamento->pisoTorre->nivel,
                'proyecto' => $apartamento->torre->proyecto->nombre,
                'ubicacion' => $apartamento->torre->proyecto->ubicacion->direccion . ', ' . 
                              $apartamento->torre->proyecto->ubicacion->ciudad->nombre,
                'estado' => $apartamento->estadoInmueble->nombre,
                'valor_total' => $apartamento->valor_total,
                'parqueaderos' => [
                    'total' => $totalParqueaderos,
                    'vehiculos' => $parqueaderosVehiculo,
                    'motos' => $parqueaderosMoto
                ]
            ]
        ], 200);
    }

    /**
     * Obtener estadísticas de apartamentos por proyecto
     */
    public function estadisticasPorProyecto(string $id_proyecto)
    {
        $apartamentos = Apartamento::whereHas('torre', function ($query) use ($id_proyecto) {
            $query->where('id_proyecto', $id_proyecto);
        })
        ->with(['estadoInmueble', 'tipoApartamento'])
        ->get();

        if ($apartamentos->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron apartamentos para este proyecto'
            ], 404);
        }

        $porEstado = $apartamentos->groupBy('id_estado_inmueble')->map(function ($group) {
            return [
                'estado' => $group->first()->estadoInmueble->nombre,
                'cantidad' => $group->count(),
                'valor_total' => $group->sum('valor_total')
            ];
        })->values();

        $porTipo = $apartamentos->groupBy('id_tipo_apartamento')->map(function ($group) {
            return [
                'tipo' => $group->first()->tipoApartamento->nombre,
                'cantidad' => $group->count(),
                'valor_promedio' => $group->avg('valor_total')
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'total_apartamentos' => $apartamentos->count(),
                'valor_total_inventario' => $apartamentos->sum('valor_total'),
                'valor_promedio' => $apartamentos->avg('valor_total'),
                'por_estado' => $porEstado,
                'por_tipo' => $porTipo
            ]
        ], 200);
    }

    /**
     * Cambiar estado de un apartamento
     */
    public function cambiarEstado(Request $request, string $id)
    {
        $apartamento = Apartamento::find($id);

        if (!$apartamento) {
            return response()->json([
                'success' => false,
                'message' => 'Apartamento no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'id_estado_inmueble' => 'required|exists:estado_inmueble,id_estado_inmueble'
        ], [
            'id_estado_inmueble.required' => 'El estado del inmueble es obligatorio',
            'id_estado_inmueble.exists' => 'El estado del inmueble seleccionado no existe'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $apartamento->update(['id_estado_inmueble' => $request->id_estado_inmueble]);
        $apartamento->load('estadoInmueble');

        return response()->json([
            'success' => true,
            'message' => 'Estado del apartamento actualizado exitosamente',
            'data' => $apartamento
        ], 200);
    }

    /**
     * Apartamentos disponibles por proyecto
     */
    public function disponiblesPorProyecto(string $id_proyecto)
    {
        // Asumiendo que existe un estado "Disponible" en estado_inmueble
        $apartamentos = Apartamento::whereHas('torre', function ($query) use ($id_proyecto) {
            $query->where('id_proyecto', $id_proyecto);
        })
        ->whereHas('estadoInmueble', function ($query) {
            $query->where('nombre', 'ILIKE', '%disponible%');
        })
        ->with([
            'tipoApartamento',
            'torre',
            'pisoTorre',
            'estadoInmueble'
        ])
        ->get();

        return response()->json([
            'success' => true,
            'data' => $apartamentos
        ], 200);
    }
}