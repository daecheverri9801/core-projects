<?php

namespace App\Http\Controllers;

use App\Models\ZonaSocial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ZonaSocialController extends Controller
{
    /**
     * Listar todas las zonas sociales
     */
    public function index()
    {
        $zonasSociales = ZonaSocial::with('proyecto.ubicacion.ciudad')->get();

        return response()->json([
            'success' => true,
            'data' => $zonasSociales
        ], 200);
    }

    /**
     * Crear una nueva zona social
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:100',
            'id_proyecto' => 'required|exists:proyecto,id_proyecto'
        ], [
            'nombre.required' => 'El nombre de la zona social es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres',
            'descripcion.max' => 'La descripción no puede exceder 100 caracteres',
            'id_proyecto.required' => 'El proyecto es obligatorio',
            'id_proyecto.exists' => 'El proyecto seleccionado no existe'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que no exista una zona social con el mismo nombre en el mismo proyecto
        $zonaSocialExistente = ZonaSocial::where('nombre', $request->nombre)
            ->where('id_proyecto', $request->id_proyecto)
            ->first();

        if ($zonaSocialExistente) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe una zona social con este nombre en el proyecto seleccionado'
            ], 409);
        }

        $zonaSocial = ZonaSocial::create($request->all());
        $zonaSocial->load('proyecto');

        return response()->json([
            'success' => true,
            'message' => 'Zona social creada exitosamente',
            'data' => $zonaSocial
        ], 201);
    }

    /**
     * Mostrar una zona social específica
     */
    public function show(string $id)
    {
        $zonaSocial = ZonaSocial::with([
            'proyecto.ubicacion.ciudad.departamento.pais',
            'proyecto.estado_proyecto'
        ])->find($id);

        if (!$zonaSocial) {
            return response()->json([
                'success' => false,
                'message' => 'Zona social no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $zonaSocial
        ], 200);
    }

    /**
     * Actualizar una zona social
     */
    public function update(Request $request, string $id)
    {
        $zonaSocial = ZonaSocial::find($id);

        if (!$zonaSocial) {
            return response()->json([
                'success' => false,
                'message' => 'Zona social no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:100',
            'id_proyecto' => 'required|exists:proyecto,id_proyecto'
        ], [
            'nombre.required' => 'El nombre de la zona social es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres',
            'descripcion.max' => 'La descripción no puede exceder 100 caracteres',
            'id_proyecto.required' => 'El proyecto es obligatorio',
            'id_proyecto.exists' => 'El proyecto seleccionado no existe'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que no exista otra zona social con el mismo nombre en el mismo proyecto
        $zonaSocialExistente = ZonaSocial::where('nombre', $request->nombre)
            ->where('id_proyecto', $request->id_proyecto)
            ->where('id_zona_social', '!=', $id)
            ->first();

        if ($zonaSocialExistente) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe otra zona social con este nombre en el proyecto seleccionado'
            ], 409);
        }

        $zonaSocial->update($request->all());
        $zonaSocial->load('proyecto');

        return response()->json([
            'success' => true,
            'message' => 'Zona social actualizada exitosamente',
            'data' => $zonaSocial
        ], 200);
    }

    /**
     * Eliminar una zona social
     */
    public function destroy(string $id)
    {
        $zonaSocial = ZonaSocial::find($id);

        if (!$zonaSocial) {
            return response()->json([
                'success' => false,
                'message' => 'Zona social no encontrada'
            ], 404);
        }

        $zonaSocial->delete();

        return response()->json([
            'success' => true,
            'message' => 'Zona social eliminada exitosamente'
        ], 200);
    }

    /**
     * Listar zonas sociales por proyecto
     */
    public function byProyecto(string $id_proyecto)
    {
        $zonasSociales = ZonaSocial::where('id_proyecto', $id_proyecto)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $zonasSociales
        ], 200);
    }

    /**
     * Buscar zonas sociales por nombre
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

        $zonasSociales = ZonaSocial::where('nombre', 'ILIKE', '%' . $request->termino . '%')
            ->orWhere('descripcion', 'ILIKE', '%' . $request->termino . '%')
            ->with('proyecto')
            ->get();

        if ($zonasSociales->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron zonas sociales con ese término de búsqueda'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $zonasSociales
        ], 200);
    }

    /**
     * Obtener resumen de una zona social
     */
    public function resumen(string $id)
    {
        $zonaSocial = ZonaSocial::with([
            'proyecto.ubicacion.ciudad',
            'proyecto.estado_proyecto'
        ])->find($id);

        if (!$zonaSocial) {
            return response()->json([
                'success' => false,
                'message' => 'Zona social no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id_zona_social' => $zonaSocial->id_zona_social,
                'nombre' => $zonaSocial->nombre,
                'descripcion' => $zonaSocial->descripcion,
                'proyecto' => [
                    'id' => $zonaSocial->proyecto->id_proyecto,
                    'nombre' => $zonaSocial->proyecto->nombre,
                    'estado' => $zonaSocial->proyecto->estado_proyecto->nombre,
                    'ubicacion' => $zonaSocial->proyecto->ubicacion->direccion . ', ' . 
                                  $zonaSocial->proyecto->ubicacion->ciudad->nombre
                ]
            ]
        ], 200);
    }

    /**
     * Obtener estadísticas de zonas sociales por proyecto
     */
    public function estadisticasPorProyecto(string $id_proyecto)
    {
        $zonasSociales = ZonaSocial::where('id_proyecto', $id_proyecto)->get();

        if ($zonasSociales->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron zonas sociales para este proyecto'
            ], 404);
        }

        $proyecto = \App\Models\Proyecto::with('ubicacion.ciudad')->find($id_proyecto);

        return response()->json([
            'success' => true,
            'data' => [
                'proyecto' => [
                    'id' => $proyecto->id_proyecto,
                    'nombre' => $proyecto->nombre,
                    'ubicacion' => $proyecto->ubicacion->direccion . ', ' . 
                                  $proyecto->ubicacion->ciudad->nombre
                ],
                'total_zonas_sociales' => $zonasSociales->count(),
                'zonas_sociales' => $zonasSociales->map(function ($zona) {
                    return [
                        'id' => $zona->id_zona_social,
                        'nombre' => $zona->nombre,
                        'descripcion' => $zona->descripcion
                    ];
                })
            ]
        ], 200);
    }

    /**
     * Crear múltiples zonas sociales para un proyecto
     */
    public function crearMultiples(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_proyecto' => 'required|exists:proyecto,id_proyecto',
            'zonas' => 'required|array|min:1',
            'zonas.*.nombre' => 'required|string|max:100',
            'zonas.*.descripcion' => 'nullable|string|max:100'
        ], [
            'id_proyecto.required' => 'El proyecto es obligatorio',
            'id_proyecto.exists' => 'El proyecto seleccionado no existe',
            'zonas.required' => 'Debe proporcionar al menos una zona social',
            'zonas.array' => 'Las zonas deben ser un arreglo',
            'zonas.min' => 'Debe proporcionar al menos una zona social',
            'zonas.*.nombre.required' => 'El nombre de cada zona social es obligatorio',
            'zonas.*.nombre.max' => 'El nombre no puede exceder 100 caracteres',
            'zonas.*.descripcion.max' => 'La descripción no puede exceder 100 caracteres'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $zonasCreadas = [];
        $zonasExistentes = [];

        foreach ($request->zonas as $zonaData) {
            // Verificar si ya existe
            $zonaExistente = ZonaSocial::where('nombre', $zonaData['nombre'])
                ->where('id_proyecto', $request->id_proyecto)
                ->first();

            if ($zonaExistente) {
                $zonasExistentes[] = $zonaData['nombre'];
                continue;
            }

            $zona = ZonaSocial::create([
                'nombre' => $zonaData['nombre'],
                'descripcion' => $zonaData['descripcion'] ?? null,
                'id_proyecto' => $request->id_proyecto
            ]);

            $zonasCreadas[] = $zona;
        }

        return response()->json([
            'success' => true,
            'message' => count($zonasCreadas) . ' zona(s) social(es) creada(s) exitosamente',
            'data' => [
                'zonas_creadas' => $zonasCreadas,
                'nombres_existentes' => $zonasExistentes
            ]
        ], 201);
    }

    /**
     * Obtener estadísticas generales de zonas sociales
     */
    public function estadisticas()
    {
        $totalZonasSociales = ZonaSocial::count();
        
        $zonasPorProyecto = ZonaSocial::selectRaw('id_proyecto, COUNT(*) as total')
            ->with('proyecto:id_proyecto,nombre')
            ->groupBy('id_proyecto')
            ->get()
            ->map(function ($item) {
                return [
                    'proyecto' => $item->proyecto->nombre,
                    'total_zonas' => $item->total
                ];
            });

        $zonaMasComun = ZonaSocial::selectRaw('nombre, COUNT(*) as total')
            ->groupBy('nombre')
            ->orderByDesc('total')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'total_zonas_sociales' => $totalZonasSociales,
                'zonas_por_proyecto' => $zonasPorProyecto,
                'zona_mas_comun' => $zonaMasComun ? [
                    'nombre' => $zonaMasComun->nombre,
                    'cantidad_proyectos' => $zonaMasComun->total
                ] : null
            ]
        ], 200);
    }

    /**
     * Listar zonas sociales comunes (presentes en múltiples proyectos)
     */
    public function zonasComunes()
    {
        $zonasComunes = ZonaSocial::selectRaw('nombre, COUNT(DISTINCT id_proyecto) as proyectos_count')
            ->groupBy('nombre')
            ->having('proyectos_count', '>', 1)
            ->orderByDesc('proyectos_count')
            ->get()
            ->map(function ($item) {
                return [
                    'nombre' => $item->nombre,
                    'cantidad_proyectos' => $item->proyectos_count
                ];
            });

        if ($zonasComunes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron zonas sociales comunes entre proyectos'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $zonasComunes
        ], 200);
    }
}