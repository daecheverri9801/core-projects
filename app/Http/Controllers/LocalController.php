<?php

namespace App\Http\Controllers;

use App\Models\Local;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocalController extends Controller
{
    /**
     * Listar todos los locales
     */
    public function index()
    {
        $locales = Local::with([
            'estadoInmueble',
            'torre.proyecto',
            'pisoTorre'
        ])->get();

        return response()->json([
            'success' => true,
            'data' => $locales
        ], 200);
    }

    /**
     * Crear un nuevo local
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'numero' => 'required|string|max:20',
            'id_estado_inmueble' => 'required|exists:estado_inmueble,id_estado_inmueble',
            'area_total_local' => 'nullable|numeric|min:0|max:99999999.99',
            'id_torre' => 'required|exists:torre,id_torre',
            'id_piso_torre' => 'required|exists:piso_torre,id_piso_torre',
            'valor_total' => 'nullable|numeric|min:0|max:999999999.99'
        ], [
            'numero.required' => 'El número del local es obligatorio',
            'numero.max' => 'El número del local no puede exceder 20 caracteres',
            'id_estado_inmueble.required' => 'El estado del inmueble es obligatorio',
            'id_estado_inmueble.exists' => 'El estado del inmueble seleccionado no existe',
            'area_total_local.numeric' => 'El área total debe ser un número',
            'area_total_local.min' => 'El área total no puede ser negativa',
            'area_total_local.max' => 'El área total excede el límite permitido',
            'id_torre.required' => 'La torre es obligatoria',
            'id_torre.exists' => 'La torre seleccionada no existe',
            'id_piso_torre.required' => 'El piso es obligatorio',
            'id_piso_torre.exists' => 'El piso seleccionado no existe',
            'valor_total.numeric' => 'El valor total debe ser un número',
            'valor_total.min' => 'El valor total no puede ser negativo',
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

        // Verificar que no exista un local con el mismo número en la misma torre
        $localExistente = Local::where('numero', $request->numero)
            ->where('id_torre', $request->id_torre)
            ->first();

        if ($localExistente) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe un local con este número en la torre seleccionada'
            ], 409);
        }

        $local = Local::create($request->all());
        $local->load([
            'estadoInmueble',
            'torre.proyecto',
            'pisoTorre'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Local creado exitosamente',
            'data' => $local
        ], 201);
    }

    /**
     * Mostrar un local específico
     */
    public function show(string $id)
    {
        $local = Local::with([
            'estadoInmueble',
            'torre.proyecto.ubicacion.ciudad',
            'pisoTorre'
        ])->find($id);

        if (!$local) {
            return response()->json([
                'success' => false,
                'message' => 'Local no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $local
        ], 200);
    }

    /**
     * Actualizar un local
     */
    public function update(Request $request, string $id)
    {
        $local = Local::find($id);

        if (!$local) {
            return response()->json([
                'success' => false,
                'message' => 'Local no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'numero' => 'required|string|max:20',
            'id_estado_inmueble' => 'required|exists:estado_inmueble,id_estado_inmueble',
            'area_total_local' => 'nullable|numeric|min:0|max:99999999.99',
            'id_torre' => 'required|exists:torre,id_torre',
            'id_piso_torre' => 'required|exists:piso_torre,id_piso_torre',
            'valor_total' => 'nullable|numeric|min:0|max:999999999.99'
        ], [
            'numero.required' => 'El número del local es obligatorio',
            'numero.max' => 'El número del local no puede exceder 20 caracteres',
            'id_estado_inmueble.required' => 'El estado del inmueble es obligatorio',
            'id_estado_inmueble.exists' => 'El estado del inmueble seleccionado no existe',
            'area_total_local.numeric' => 'El área total debe ser un número',
            'area_total_local.min' => 'El área total no puede ser negativa',
            'area_total_local.max' => 'El área total excede el límite permitido',
            'id_torre.required' => 'La torre es obligatoria',
            'id_torre.exists' => 'La torre seleccionada no existe',
            'id_piso_torre.required' => 'El piso es obligatorio',
            'id_piso_torre.exists' => 'El piso seleccionado no existe',
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

        // Verificar que no exista otro local con el mismo número en la misma torre
        $localExistente = Local::where('numero', $request->numero)
            ->where('id_torre', $request->id_torre)
            ->where('id_local', '!=', $id)
            ->first();

        if ($localExistente) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe otro local con este número en la torre seleccionada'
            ], 409);
        }

        $local->update($request->all());
        $local->load([
            'estadoInmueble',
            'torre.proyecto',
            'pisoTorre'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Local actualizado exitosamente',
            'data' => $local
        ], 200);
    }

    /**
     * Eliminar un local
     */
    public function destroy(string $id)
    {
        $local = Local::find($id);

        if (!$local) {
            return response()->json([
                'success' => false,
                'message' => 'Local no encontrado'
            ], 404);
        }

        $local->delete();

        return response()->json([
            'success' => true,
            'message' => 'Local eliminado exitosamente'
        ], 200);
    }

    /**
     * Listar locales por torre
     */
    public function byTorre(string $id_torre)
    {
        $locales = Local::where('id_torre', $id_torre)
            ->with([
                'estadoInmueble',
                'pisoTorre'
            ])
            ->orderBy('id_piso_torre')
            ->orderBy('numero')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $locales
        ], 200);
    }

    /**
     * Listar locales por piso
     */
    public function byPiso(string $id_piso_torre)
    {
        $locales = Local::where('id_piso_torre', $id_piso_torre)
            ->with([
                'estadoInmueble',
                'torre'
            ])
            ->orderBy('numero')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $locales
        ], 200);
    }

    /**
     * Listar locales por estado
     */
    public function byEstado(string $id_estado_inmueble)
    {
        $locales = Local::where('id_estado_inmueble', $id_estado_inmueble)
            ->with([
                'estadoInmueble',
                'torre.proyecto',
                'pisoTorre'
            ])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $locales
        ], 200);
    }

    /**
     * Listar locales por proyecto
     */
    public function byProyecto(string $id_proyecto)
    {
        $locales = Local::whereHas('torre', function ($query) use ($id_proyecto) {
            $query->where('id_proyecto', $id_proyecto);
        })
        ->with([
            'estadoInmueble',
            'torre',
            'pisoTorre'
        ])
        ->get();

        return response()->json([
            'success' => true,
            'data' => $locales
        ], 200);
    }

    /**
     * Buscar locales por número
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

        $locales = Local::where('numero', 'ILIKE', '%' . $request->termino . '%')
            ->with([
                'estadoInmueble',
                'torre.proyecto',
                'pisoTorre'
            ])
            ->get();

        if ($locales->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron locales con ese término de búsqueda'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $locales
        ], 200);
    }

    /**
     * Obtener resumen de un local
     */
    public function resumen(string $id)
    {
        $local = Local::with([
            'estadoInmueble',
            'torre.proyecto.ubicacion.ciudad',
            'pisoTorre'
        ])->find($id);

        if (!$local) {
            return response()->json([
                'success' => false,
                'message' => 'Local no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id_local' => $local->id_local,
                'numero' => $local->numero,
                'area_total' => $local->area_total_local,
                'torre' => $local->torre->nombre_torre,
                'piso' => $local->pisoTorre->nivel,
                'proyecto' => $local->torre->proyecto->nombre,
                'ubicacion' => $local->torre->proyecto->ubicacion->direccion . ', ' . 
                              $local->torre->proyecto->ubicacion->ciudad->nombre,
                'estado' => $local->estadoInmueble->nombre,
                'valor_total' => $local->valor_total
            ]
        ], 200);
    }

    /**
     * Obtener estadísticas de locales por proyecto
     */
    public function estadisticasPorProyecto(string $id_proyecto)
    {
        $locales = Local::whereHas('torre', function ($query) use ($id_proyecto) {
            $query->where('id_proyecto', $id_proyecto);
        })
        ->with('estadoInmueble')
        ->get();

        if ($locales->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron locales para este proyecto'
            ], 404);
        }

        $porEstado = $locales->groupBy('id_estado_inmueble')->map(function ($group) {
            return [
                'estado' => $group->first()->estadoInmueble->nombre,
                'cantidad' => $group->count(),
                'area_total' => $group->sum('area_total_local'),
                'valor_total' => $group->sum('valor_total')
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'total_locales' => $locales->count(),
                'area_total' => $locales->sum('area_total_local'),
                'area_promedio' => $locales->avg('area_total_local'),
                'por_estado' => $porEstado
            ]
        ], 200);
    }

    /**
     * Cambiar estado de un local
     */
    public function cambiarEstado(Request $request, string $id)
    {
        $local = Local::find($id);

        if (!$local) {
            return response()->json([
                'success' => false,
                'message' => 'Local no encontrado'
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

        $local->update(['id_estado_inmueble' => $request->id_estado_inmueble]);
        $local->load('estadoInmueble');

        return response()->json([
            'success' => true,
            'message' => 'Estado del local actualizado exitosamente',
            'data' => $local
        ], 200);
    }

    /**
     * Locales disponibles por proyecto
     */
    public function disponiblesPorProyecto(string $id_proyecto)
    {
        $locales = Local::whereHas('torre', function ($query) use ($id_proyecto) {
            $query->where('id_proyecto', $id_proyecto);
        })
        ->whereHas('estadoInmueble', function ($query) {
            $query->where('nombre', 'ILIKE', '%disponible%');
        })
        ->with([
            'estadoInmueble',
            'torre',
            'pisoTorre'
        ])
        ->get();

        return response()->json([
            'success' => true,
            'data' => $locales
        ], 200);
    }

    /**
     * Locales por rango de área
     */
    public function byRangoArea(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'area_min' => 'required|numeric|min:0',
            'area_max' => 'required|numeric|gte:area_min'
        ], [
            'area_min.required' => 'El área mínima es obligatoria',
            'area_min.numeric' => 'El área mínima debe ser un número',
            'area_min.min' => 'El área mínima no puede ser negativa',
            'area_max.required' => 'El área máxima es obligatoria',
            'area_max.numeric' => 'El área máxima debe ser un número',
            'area_max.gte' => 'El área máxima debe ser mayor o igual al área mínima'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $locales = Local::whereBetween('area_total_local', [$request->area_min, $request->area_max])
            ->with([
                'estadoInmueble',
                'torre.proyecto',
                'pisoTorre'
            ])
            ->get();

        if ($locales->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron locales en ese rango de área'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $locales
        ], 200);
    }
}