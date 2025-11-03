<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProyectoController extends Controller
{
    /**
     * Listar todos los proyectos
     */
    public function index()
    {
        $proyectos = Proyecto::with([
            'estado_proyecto',
            'ubicacion.ciudad.departamento.pais',
            'torres',
            'zonasSociales'
        ])->get();

        return response()->json([
            'success' => true,
            'data' => $proyectos
        ], 200);
    }

    /**
     * Crear un nuevo proyecto
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:300',
            'fecha_inicio' => 'nullable|date',
            'fecha_finalizacion' => 'nullable|date|after_or_equal:fecha_inicio',
            'presupuesto_inicial' => 'nullable|numeric|min:0|max:9999999999999999.99',
            'presupuesto_final' => 'nullable|numeric|min:0|max:9999999999999999.99',
            'metros_construidos' => 'nullable|numeric|min:0|max:9999999999999999.99',
            'cantidad_locales' => 'nullable|integer|min:0',
            'cantidad_apartamentos' => 'nullable|integer|min:0',
            'cantidad_parqueaderos_vehiculo' => 'nullable|integer|min:0',
            'cantidad_parqueaderos_moto' => 'nullable|integer|min:0',
            'estrato' => 'nullable|integer|min:1|max:6',
            'numero_pisos' => 'nullable|integer|min:1|max:32767',
            'numero_torres' => 'nullable|integer|min:1|max:32767',
            'porcentaje_cuota_inicial_min' => 'nullable|numeric|min:0|max:100',
            'valor_min_separacion' => 'nullable|numeric|min:0|max:9999999999999999.99',
            'plazo_cuota_inicial_meses' => 'nullable|integer|min:1|max:32767',
            'id_estado' => 'required|exists:estado,id_estado',
            'id_ubicacion' => 'required|exists:ubicacion,id_ubicacion'
        ], [
            'nombre.required' => 'El nombre del proyecto es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 150 caracteres',
            'descripcion.max' => 'La descripción no puede exceder 300 caracteres',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida',
            'fecha_finalizacion.date' => 'La fecha de finalización debe ser una fecha válida',
            'fecha_finalizacion.after_or_equal' => 'La fecha de finalización debe ser posterior o igual a la fecha de inicio',
            'presupuesto_inicial.numeric' => 'El presupuesto inicial debe ser un valor numérico',
            'presupuesto_inicial.min' => 'El presupuesto inicial no puede ser negativo',
            'presupuesto_final.numeric' => 'El presupuesto final debe ser un valor numérico',
            'presupuesto_final.min' => 'El presupuesto final no puede ser negativo',
            'metros_construidos.numeric' => 'Los metros construidos deben ser un valor numérico',
            'metros_construidos.min' => 'Los metros construidos no pueden ser negativos',
            'estrato.min' => 'El estrato debe ser entre 1 y 6',
            'estrato.max' => 'El estrato debe ser entre 1 y 6',
            'porcentaje_cuota_inicial_min.min' => 'El porcentaje no puede ser negativo',
            'porcentaje_cuota_inicial_min.max' => 'El porcentaje no puede ser mayor a 100',
            'id_estado.required' => 'El estado del proyecto es obligatorio',
            'id_estado.exists' => 'El estado seleccionado no existe',
            'id_ubicacion.required' => 'La ubicación del proyecto es obligatoria',
            'id_ubicacion.exists' => 'La ubicación seleccionada no existe'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $proyecto = Proyecto::create($request->all());
        $proyecto->load(['estado_proyecto', 'ubicacion.ciudad.departamento.pais']);

        return response()->json([
            'success' => true,
            'message' => 'Proyecto creado exitosamente',
            'data' => $proyecto
        ], 201);
    }

    /**
     * Mostrar un proyecto específico
     */
    public function show(string $id)
    {
        $proyecto = Proyecto::with([
            'estado_proyecto',
            'ubicacion.ciudad.departamento.pais',
            'torres.pisos',
            'zonasSociales',
            'politicasPrecios',
            'politicasComisiones'
        ])->find($id);

        if (!$proyecto) {
            return response()->json([
                'success' => false,
                'message' => 'Proyecto no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $proyecto
        ], 200);
    }

    /**
     * Actualizar un proyecto
     */
    public function update(Request $request, string $id)
    {
        $proyecto = Proyecto::find($id);

        if (!$proyecto) {
            return response()->json([
                'success' => false,
                'message' => 'Proyecto no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:300',
            'fecha_inicio' => 'nullable|date',
            'fecha_finalizacion' => 'nullable|date|after_or_equal:fecha_inicio',
            'presupuesto_inicial' => 'nullable|numeric|min:0|max:9999999999999999.99',
            'presupuesto_final' => 'nullable|numeric|min:0|max:9999999999999999.99',
            'metros_construidos' => 'nullable|numeric|min:0|max:9999999999999999.99',
            'cantidad_locales' => 'nullable|integer|min:0',
            'cantidad_apartamentos' => 'nullable|integer|min:0',
            'cantidad_parqueaderos_vehiculo' => 'nullable|integer|min:0',
            'cantidad_parqueaderos_moto' => 'nullable|integer|min:0',
            'estrato' => 'nullable|integer|min:1|max:6',
            'numero_pisos' => 'nullable|integer|min:1|max:32767',
            'numero_torres' => 'nullable|integer|min:1|max:32767',
            'porcentaje_cuota_inicial_min' => 'nullable|numeric|min:0|max:100',
            'valor_min_separacion' => 'nullable|numeric|min:0|max:9999999999999999.99',
            'plazo_cuota_inicial_meses' => 'nullable|integer|min:1|max:32767',
            'id_estado' => 'required|exists:estado,id_estado',
            'id_ubicacion' => 'required|exists:ubicacion,id_ubicacion'
        ], [
            'nombre.required' => 'El nombre del proyecto es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 150 caracteres',
            'descripcion.max' => 'La descripción no puede exceder 300 caracteres',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida',
            'fecha_finalizacion.date' => 'La fecha de finalización debe ser una fecha válida',
            'fecha_finalizacion.after_or_equal' => 'La fecha de finalización debe ser posterior o igual a la fecha de inicio',
            'presupuesto_inicial.numeric' => 'El presupuesto inicial debe ser un valor numérico',
            'presupuesto_inicial.min' => 'El presupuesto inicial no puede ser negativo',
            'presupuesto_final.numeric' => 'El presupuesto final debe ser un valor numérico',
            'presupuesto_final.min' => 'El presupuesto final no puede ser negativo',
            'metros_construidos.numeric' => 'Los metros construidos deben ser un valor numérico',
            'metros_construidos.min' => 'Los metros construidos no pueden ser negativos',
            'estrato.min' => 'El estrato debe ser entre 1 y 6',
            'estrato.max' => 'El estrato debe ser entre 1 y 6',
            'porcentaje_cuota_inicial_min.min' => 'El porcentaje no puede ser negativo',
            'porcentaje_cuota_inicial_min.max' => 'El porcentaje no puede ser mayor a 100',
            'id_estado.required' => 'El estado del proyecto es obligatorio',
            'id_estado.exists' => 'El estado seleccionado no existe',
            'id_ubicacion.required' => 'La ubicación del proyecto es obligatoria',
            'id_ubicacion.exists' => 'La ubicación seleccionada no existe'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $proyecto->update($request->all());
        $proyecto->load(['estado_proyecto', 'ubicacion.ciudad.departamento.pais']);

        return response()->json([
            'success' => true,
            'message' => 'Proyecto actualizado exitosamente',
            'data' => $proyecto
        ], 200);
    }

    /**
     * Eliminar un proyecto
     */
    public function destroy(string $id)
    {
        $proyecto = Proyecto::find($id);

        if (!$proyecto) {
            return response()->json([
                'success' => false,
                'message' => 'Proyecto no encontrado'
            ], 404);
        }

        // Verificar si tiene torres asociadas
        if ($proyecto->torres()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el proyecto porque tiene torres asociadas'
            ], 409);
        }

        $proyecto->delete();

        return response()->json([
            'success' => true,
            'message' => 'Proyecto eliminado exitosamente'
        ], 200);
    }

    /**
     * Listar proyectos por estado
     */
    public function byEstado(string $id_estado)
    {
        $proyectos = Proyecto::where('id_estado', $id_estado)
            ->with(['estado_proyecto', 'ubicacion.ciudad'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $proyectos
        ], 200);
    }

    /**
     * Listar proyectos por ciudad
     */
    public function byCiudad(string $id_ciudad)
    {
        $proyectos = Proyecto::whereHas('ubicacion', function ($query) use ($id_ciudad) {
            $query->where('id_ciudad', $id_ciudad);
        })
        ->with(['estado_proyecto', 'ubicacion.ciudad'])
        ->get();

        return response()->json([
            'success' => true,
            'data' => $proyectos
        ], 200);
    }

    /**
     * Buscar proyectos por nombre
     */
    public function buscar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'termino' => 'required|string|min:2'
        ], [
            'termino.required' => 'El término de búsqueda es obligatorio',
            'termino.min' => 'El término de búsqueda debe tener al menos 2 caracteres'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $proyectos = Proyecto::where('nombre', 'ILIKE', '%' . $request->termino . '%')
            ->with(['estado_proyecto', 'ubicacion.ciudad'])
            ->get();

        if ($proyectos->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron proyectos con ese término de búsqueda'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $proyectos
        ], 200);
    }

    /**
     * Obtener resumen del proyecto
     */
    public function resumen(string $id)
    {
        $proyecto = Proyecto::with([
            'estado_proyecto',
            'ubicacion.ciudad.departamento.pais',
            'torres',
            'zonasSociales'
        ])->find($id);

        if (!$proyecto) {
            return response()->json([
                'success' => false,
                'message' => 'Proyecto no encontrado'
            ], 404);
        }

        $totalTorres = $proyecto->torres->count();
        $totalZonasSociales = $proyecto->zonasSociales->count();

        return response()->json([
            'success' => true,
            'data' => [
                'id_proyecto' => $proyecto->id_proyecto,
                'nombre' => $proyecto->nombre,
                'descripcion' => $proyecto->descripcion,
                'estado' => $proyecto->estado_proyecto->nombre,
                'ubicacion' => $proyecto->ubicacion->direccion . ', ' . $proyecto->ubicacion->ciudad->nombre,
                'fecha_inicio' => $proyecto->fecha_inicio,
                'fecha_finalizacion' => $proyecto->fecha_finalizacion,
                'presupuesto_inicial' => $proyecto->presupuesto_inicial,
                'presupuesto_final' => $proyecto->presupuesto_final,
                'metros_construidos' => $proyecto->metros_construidos,
                'estrato' => $proyecto->estrato,
                'inventario' => [
                    'locales' => $proyecto->cantidad_locales,
                    'apartamentos' => $proyecto->cantidad_apartamentos,
                    'parqueaderos_vehiculo' => $proyecto->cantidad_parqueaderos_vehiculo,
                    'parqueaderos_moto' => $proyecto->cantidad_parqueaderos_moto
                ],
                'estructura' => [
                    'numero_torres' => $proyecto->numero_torres,
                    'torres_registradas' => $totalTorres,
                    'numero_pisos' => $proyecto->numero_pisos,
                    'zonas_sociales' => $totalZonasSociales
                ],
                'financiacion' => [
                    'porcentaje_cuota_inicial_min' => $proyecto->porcentaje_cuota_inicial_min,
                    'valor_min_separacion' => $proyecto->valor_min_separacion,
                    'plazo_cuota_inicial_meses' => $proyecto->plazo_cuota_inicial_meses
                ]
            ]
        ], 200);
    }

    /**
     * Obtener estadísticas generales de proyectos
     */
    public function estadisticas()
    {
        $totalProyectos = Proyecto::count();
        
        $proyectosPorEstado = Proyecto::selectRaw('id_estado, COUNT(*) as total')
            ->with('estado_proyecto:id_estado,nombre')
            ->groupBy('id_estado')
            ->get()
            ->map(function ($item) {
                return [
                    'estado' => $item->estado_proyecto->nombre,
                    'total' => $item->total
                ];
            });

        $presupuestoTotal = Proyecto::sum('presupuesto_inicial');
        $metrosTotales = Proyecto::sum('metros_construidos');
        $totalApartamentos = Proyecto::sum('cantidad_apartamentos');
        $totalLocales = Proyecto::sum('cantidad_locales');

        return response()->json([
            'success' => true,
            'data' => [
                'total_proyectos' => $totalProyectos,
                'proyectos_por_estado' => $proyectosPorEstado,
                'presupuesto_total' => $presupuestoTotal,
                'metros_totales_construidos' => $metrosTotales,
                'total_apartamentos' => $totalApartamentos,
                'total_locales' => $totalLocales
            ]
        ], 200);
    }
}