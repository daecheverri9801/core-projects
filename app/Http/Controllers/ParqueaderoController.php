<?php

namespace App\Http\Controllers;

use App\Models\Parqueadero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ParqueaderoController extends Controller
{
    /**
     * Listar todos los parqueaderos
     */
    public function index()
    {
        $parqueaderos = Parqueadero::with([
            'apartamento.torre.proyecto',
            'apartamento.pisoTorre'
        ])->get();

        return response()->json([
            'success' => true,
            'data' => $parqueaderos
        ], 200);
    }

    /**
     * Crear un nuevo parqueadero
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'numero' => 'required|string|max:20|unique:parqueadero,numero',
            'tipo' => 'required|string|max:20|in:Vehiculo,Moto',
            'id_apartamento' => 'nullable|exists:apartamento,id_apartamento'
        ], [
            'numero.required' => 'El número del parqueadero es obligatorio',
            'numero.max' => 'El número del parqueadero no puede exceder 20 caracteres',
            'numero.unique' => 'Ya existe un parqueadero con este número',
            'tipo.required' => 'El tipo de parqueadero es obligatorio',
            'tipo.max' => 'El tipo no puede exceder 20 caracteres',
            'tipo.in' => 'El tipo debe ser Vehiculo o Moto',
            'id_apartamento.exists' => 'El apartamento seleccionado no existe'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $parqueadero = Parqueadero::create($request->all());
        $parqueadero->load('apartamento.torre.proyecto');

        return response()->json([
            'success' => true,
            'message' => 'Parqueadero creado exitosamente',
            'data' => $parqueadero
        ], 201);
    }

    /**
     * Mostrar un parqueadero específico
     */
    public function show(string $id)
    {
        $parqueadero = Parqueadero::with([
            'apartamento.torre.proyecto.ubicacion.ciudad',
            'apartamento.pisoTorre',
            'apartamento.tipoApartamento',
            'apartamento.estadoInmueble'
        ])->find($id);

        if (!$parqueadero) {
            return response()->json([
                'success' => false,
                'message' => 'Parqueadero no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $parqueadero
        ], 200);
    }

    /**
     * Actualizar un parqueadero
     */
    public function update(Request $request, string $id)
    {
        $parqueadero = Parqueadero::find($id);

        if (!$parqueadero) {
            return response()->json([
                'success' => false,
                'message' => 'Parqueadero no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'numero' => 'required|string|max:20|unique:parqueadero,numero,' . $id . ',id_parqueadero',
            'tipo' => 'required|string|max:20|in:Vehiculo,Moto',
            'id_apartamento' => 'nullable|exists:apartamento,id_apartamento'
        ], [
            'numero.required' => 'El número del parqueadero es obligatorio',
            'numero.max' => 'El número del parqueadero no puede exceder 20 caracteres',
            'numero.unique' => 'Ya existe otro parqueadero con este número',
            'tipo.required' => 'El tipo de parqueadero es obligatorio',
            'tipo.max' => 'El tipo no puede exceder 20 caracteres',
            'tipo.in' => 'El tipo debe ser Vehiculo o Moto',
            'id_apartamento.exists' => 'El apartamento seleccionado no existe'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $parqueadero->update($request->all());
        $parqueadero->load('apartamento.torre.proyecto');

        return response()->json([
            'success' => true,
            'message' => 'Parqueadero actualizado exitosamente',
            'data' => $parqueadero
        ], 200);
    }

    /**
     * Eliminar un parqueadero
     */
    public function destroy(string $id)
    {
        $parqueadero = Parqueadero::find($id);

        if (!$parqueadero) {
            return response()->json([
                'success' => false,
                'message' => 'Parqueadero no encontrado'
            ], 404);
        }

        $parqueadero->delete();

        return response()->json([
            'success' => true,
            'message' => 'Parqueadero eliminado exitosamente'
        ], 200);
    }

    /**
     * Listar parqueaderos por tipo
     */
    public function byTipo(string $tipo)
    {
        if (!in_array($tipo, ['Vehiculo', 'Moto'])) {
            return response()->json([
                'success' => false,
                'message' => 'El tipo debe ser Vehiculo o Moto'
            ], 422);
        }

        $parqueaderos = Parqueadero::where('tipo', $tipo)
            ->with('apartamento.torre.proyecto')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $parqueaderos
        ], 200);
    }

    /**
     * Listar parqueaderos por apartamento
     */
    public function byApartamento(string $id_apartamento)
    {
        $parqueaderos = Parqueadero::where('id_apartamento', $id_apartamento)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $parqueaderos
        ], 200);
    }

    /**
     * Listar parqueaderos disponibles (sin apartamento asignado)
     */
    public function disponibles()
    {
        $parqueaderos = Parqueadero::whereNull('id_apartamento')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $parqueaderos
        ], 200);
    }

    /**
     * Listar parqueaderos asignados
     */
    public function asignados()
    {
        $parqueaderos = Parqueadero::whereNotNull('id_apartamento')
            ->with('apartamento.torre.proyecto')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $parqueaderos
        ], 200);
    }

    /**
     * Asignar parqueadero a un apartamento
     */
    public function asignar(Request $request, string $id)
    {
        $parqueadero = Parqueadero::find($id);

        if (!$parqueadero) {
            return response()->json([
                'success' => false,
                'message' => 'Parqueadero no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'id_apartamento' => 'required|exists:apartamento,id_apartamento'
        ], [
            'id_apartamento.required' => 'El apartamento es obligatorio',
            'id_apartamento.exists' => 'El apartamento seleccionado no existe'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if ($parqueadero->id_apartamento) {
            return response()->json([
                'success' => false,
                'message' => 'El parqueadero ya está asignado a un apartamento'
            ], 409);
        }

        $parqueadero->update(['id_apartamento' => $request->id_apartamento]);
        $parqueadero->load('apartamento.torre.proyecto');

        return response()->json([
            'success' => true,
            'message' => 'Parqueadero asignado exitosamente',
            'data' => $parqueadero
        ], 200);
    }

    /**
     * Desasignar parqueadero de un apartamento
     */
    public function desasignar(string $id)
    {
        $parqueadero = Parqueadero::find($id);

        if (!$parqueadero) {
            return response()->json([
                'success' => false,
                'message' => 'Parqueadero no encontrado'
            ], 404);
        }

        if (!$parqueadero->id_apartamento) {
            return response()->json([
                'success' => false,
                'message' => 'El parqueadero no está asignado a ningún apartamento'
            ], 409);
        }

        $parqueadero->update(['id_apartamento' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Parqueadero desasignado exitosamente',
            'data' => $parqueadero
        ], 200);
    }

    /**
     * Buscar parqueaderos por número
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

        $parqueaderos = Parqueadero::where('numero', 'ILIKE', '%' . $request->termino . '%')
            ->with('apartamento.torre.proyecto')
            ->get();

        if ($parqueaderos->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron parqueaderos con ese término de búsqueda'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $parqueaderos
        ], 200);
    }

    /**
     * Obtener resumen de un parqueadero
     */
    public function resumen(string $id)
    {
        $parqueadero = Parqueadero::with([
            'apartamento.torre.proyecto.ubicacion.ciudad',
            'apartamento.pisoTorre',
            'apartamento.tipoApartamento'
        ])->find($id);

        if (!$parqueadero) {
            return response()->json([
                'success' => false,
                'message' => 'Parqueadero no encontrado'
            ], 404);
        }

        $resumen = [
            'id_parqueadero' => $parqueadero->id_parqueadero,
            'numero' => $parqueadero->numero,
            'tipo' => $parqueadero->tipo,
            'estado' => $parqueadero->id_apartamento ? 'Asignado' : 'Disponible'
        ];

        if ($parqueadero->apartamento) {
            $resumen['apartamento'] = [
                'numero' => $parqueadero->apartamento->numero,
                'tipo' => $parqueadero->apartamento->tipoApartamento->nombre,
                'torre' => $parqueadero->apartamento->torre->nombre_torre,
                'piso' => $parqueadero->apartamento->pisoTorre->nivel,
                'proyecto' => $parqueadero->apartamento->torre->proyecto->nombre,
                'ubicacion' => $parqueadero->apartamento->torre->proyecto->ubicacion->direccion . ', ' . 
                              $parqueadero->apartamento->torre->proyecto->ubicacion->ciudad->nombre
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $resumen
        ], 200);
    }

    /**
     * Obtener estadísticas generales de parqueaderos
     */
    public function estadisticas()
    {
        $totalParqueaderos = Parqueadero::count();
        $disponibles = Parqueadero::whereNull('id_apartamento')->count();
        $asignados = Parqueadero::whereNotNull('id_apartamento')->count();
        
        $porTipo = Parqueadero::selectRaw('tipo, COUNT(*) as total')
            ->groupBy('tipo')
            ->get()
            ->map(function ($item) {
                return [
                    'tipo' => $item->tipo,
                    'total' => $item->total
                ];
            });

        $vehiculosDisponibles = Parqueadero::where('tipo', 'Vehiculo')
            ->whereNull('id_apartamento')
            ->count();
        
        $motosDisponibles = Parqueadero::where('tipo', 'Moto')
            ->whereNull('id_apartamento')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_parqueaderos' => $totalParqueaderos,
                'disponibles' => $disponibles,
                'asignados' => $asignados,
                'por_tipo' => $porTipo,
                'disponibles_por_tipo' => [
                    'vehiculos' => $vehiculosDisponibles,
                    'motos' => $motosDisponibles
                ]
            ]
        ], 200);
    }

    /**
     * Parqueaderos disponibles por tipo
     */
    public function disponiblesPorTipo(string $tipo)
    {
        if (!in_array($tipo, ['Vehiculo', 'Moto'])) {
            return response()->json([
                'success' => false,
                'message' => 'El tipo debe ser Vehiculo o Moto'
            ], 422);
        }

        $parqueaderos = Parqueadero::where('tipo', $tipo)
            ->whereNull('id_apartamento')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $parqueaderos
        ], 200);
    }

    /**
     * Crear múltiples parqueaderos
     */
    public function crearMultiples(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'prefijo' => 'required|string|max:10',
            'numero_inicio' => 'required|integer|min:1',
            'numero_fin' => 'required|integer|gte:numero_inicio',
            'tipo' => 'required|string|in:Vehiculo,Moto'
        ], [
            'prefijo.required' => 'El prefijo es obligatorio',
            'prefijo.max' => 'El prefijo no puede exceder 10 caracteres',
            'numero_inicio.required' => 'El número de inicio es obligatorio',
            'numero_inicio.integer' => 'El número de inicio debe ser un entero',
            'numero_inicio.min' => 'El número de inicio debe ser al menos 1',
            'numero_fin.required' => 'El número final es obligatorio',
            'numero_fin.integer' => 'El número final debe ser un entero',
            'numero_fin.gte' => 'El número final debe ser mayor o igual al número de inicio',
            'tipo.required' => 'El tipo es obligatorio',
            'tipo.in' => 'El tipo debe ser Vehiculo o Moto'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $parqueaderosCreados = [];
        $parqueaderosExistentes = [];

        for ($numero = $request->numero_inicio; $numero <= $request->numero_fin; $numero++) {
            $numeroParqueadero = $request->prefijo . $numero;

            // Verificar si ya existe
            $parqueaderoExistente = Parqueadero::where('numero', $numeroParqueadero)->first();

            if ($parqueaderoExistente) {
                $parqueaderosExistentes[] = $numeroParqueadero;
                continue;
            }

            $parqueadero = Parqueadero::create([
                'numero' => $numeroParqueadero,
                'tipo' => $request->tipo,
                'id_apartamento' => null
            ]);

            $parqueaderosCreados[] = $parqueadero;
        }

        return response()->json([
            'success' => true,
            'message' => count($parqueaderosCreados) . ' parqueadero(s) creado(s) exitosamente',
            'data' => [
                'parqueaderos_creados' => $parqueaderosCreados,
                'numeros_existentes' => $parqueaderosExistentes
            ]
        ], 201);
    }
}