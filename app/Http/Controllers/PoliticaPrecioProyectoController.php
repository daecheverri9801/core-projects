<?php

namespace App\Http\Controllers;

use App\Models\PoliticaPrecioProyecto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PoliticaPrecioProyectoController extends Controller
{
    /**
     * Listar todas las políticas de precio
     */
    public function index()
    {
        $politicas = PoliticaPrecioProyecto::with('proyecto.ubicacion.ciudad')
            ->orderBy('aplica_desde', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $politicas
        ], 200);
    }

    /**
     * Crear una nueva política de precio
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_proyecto' => 'required|exists:proyecto,id_proyecto',
            'ventas_por_escalon' => 'nullable|integer|min:1',
            'porcentaje_aumento' => 'nullable|numeric|min:0|max:999.999',
            'aplica_desde' => 'nullable|date',
            'estado' => 'boolean'
        ], [
            'id_proyecto.required' => 'El proyecto es obligatorio',
            'id_proyecto.exists' => 'El proyecto seleccionado no existe',
            'ventas_por_escalon.integer' => 'Las ventas por escalón deben ser un número entero',
            'ventas_por_escalon.min' => 'Las ventas por escalón deben ser al menos 1',
            'porcentaje_aumento.numeric' => 'El porcentaje de aumento debe ser un número',
            'porcentaje_aumento.min' => 'El porcentaje de aumento no puede ser negativo',
            'porcentaje_aumento.max' => 'El porcentaje de aumento excede el límite permitido (999.999%)',
            'aplica_desde.date' => 'La fecha de aplicación debe ser una fecha válida',
            'estado.boolean' => 'El estado debe ser verdadero o falso'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $politica = PoliticaPrecioProyecto::create($request->all());
        $politica->load('proyecto');

        return response()->json([
            'success' => true,
            'message' => 'Política de precio creada exitosamente',
            'data' => $politica
        ], 201);
    }

    /**
     * Mostrar una política de precio específica
     */
    public function show(string $id)
    {
        $politica = PoliticaPrecioProyecto::with([
            'proyecto.ubicacion.ciudad.departamento.pais',
            'proyecto.estado_proyecto'
        ])->find($id);

        if (!$politica) {
            return response()->json([
                'success' => false,
                'message' => 'Política de precio no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $politica
        ], 200);
    }

    /**
     * Actualizar una política de precio
     */
    public function update(Request $request, string $id)
    {
        $politica = PoliticaPrecioProyecto::find($id);

        if (!$politica) {
            return response()->json([
                'success' => false,
                'message' => 'Política de precio no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'id_proyecto' => 'required|exists:proyecto,id_proyecto',
            'ventas_por_escalon' => 'nullable|integer|min:1',
            'porcentaje_aumento' => 'nullable|numeric|min:0|max:999.999',
            'aplica_desde' => 'nullable|date',
            'estado' => 'boolean'
        ], [
            'id_proyecto.required' => 'El proyecto es obligatorio',
            'id_proyecto.exists' => 'El proyecto seleccionado no existe',
            'ventas_por_escalon.integer' => 'Las ventas por escalón deben ser un número entero',
            'ventas_por_escalon.min' => 'Las ventas por escalón deben ser al menos 1',
            'porcentaje_aumento.numeric' => 'El porcentaje de aumento debe ser un número',
            'porcentaje_aumento.min' => 'El porcentaje de aumento no puede ser negativo',
            'porcentaje_aumento.max' => 'El porcentaje de aumento excede el límite permitido (999.999%)',
            'aplica_desde.date' => 'La fecha de aplicación debe ser una fecha válida',
            'estado.boolean' => 'El estado debe ser verdadero o falso'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $politica->update($request->all());
        $politica->load('proyecto');

        return response()->json([
            'success' => true,
            'message' => 'Política de precio actualizada exitosamente',
            'data' => $politica
        ], 200);
    }

    /**
     * Eliminar una política de precio
     */
    public function destroy(string $id)
    {
        $politica = PoliticaPrecioProyecto::find($id);

        if (!$politica) {
            return response()->json([
                'success' => false,
                'message' => 'Política de precio no encontrada'
            ], 404);
        }

        $politica->delete();

        return response()->json([
            'success' => true,
            'message' => 'Política de precio eliminada exitosamente'
        ], 200);
    }

    /**
     * Listar políticas de precio por proyecto
     */
    public function byProyecto(string $id_proyecto)
    {
        $politicas = PoliticaPrecioProyecto::where('id_proyecto', $id_proyecto)
            ->orderBy('aplica_desde', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $politicas
        ], 200);
    }

    /**
     * Listar políticas activas
     */
    public function activas()
    {
        $politicas = PoliticaPrecioProyecto::where('estado', true)
            ->with('proyecto')
            ->orderBy('aplica_desde', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $politicas
        ], 200);
    }

    /**
     * Listar políticas inactivas
     */
    public function inactivas()
    {
        $politicas = PoliticaPrecioProyecto::where('estado', false)
            ->with('proyecto')
            ->orderBy('aplica_desde', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $politicas
        ], 200);
    }

    /**
     * Cambiar estado de una política
     */
    public function cambiarEstado(Request $request, string $id)
    {
        $politica = PoliticaPrecioProyecto::find($id);

        if (!$politica) {
            return response()->json([
                'success' => false,
                'message' => 'Política de precio no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'estado' => 'required|boolean'
        ], [
            'estado.required' => 'El estado es obligatorio',
            'estado.boolean' => 'El estado debe ser verdadero o falso'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $politica->update(['estado' => $request->estado]);
        $politica->load('proyecto');

        return response()->json([
            'success' => true,
            'message' => 'Estado de la política actualizado exitosamente',
            'data' => $politica
        ], 200);
    }

    /**
     * Obtener política activa vigente para un proyecto
     */
    public function vigentePorProyecto(string $id_proyecto)
    {
        $politica = PoliticaPrecioProyecto::where('id_proyecto', $id_proyecto)
            ->where('estado', true)
            ->where(function ($query) {
                $query->whereNull('aplica_desde')
                    ->orWhere('aplica_desde', '<=', Carbon::now());
            })
            ->orderBy('aplica_desde', 'desc')
            ->first();

        if (!$politica) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró una política de precio vigente para este proyecto'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $politica
        ], 200);
    }

    /**
     * Obtener políticas futuras (que aún no aplican)
     */
    // public function futuras()
    // {
    //     $politicas = PoliticaPrecioProyecto::where('estado', true)
    //         ->whereNotNull('aplica_desde')
    //         ->where('aplica_desde', '>', Carbon::now())
    //         ->with('proyecto')
    //         ->orderBy('aplica_desde', 'asc')
    //         ->get();

    //     return response()->json([
    //         'success' => true,
    //         'data' => $politicas
    //     ], 200);
    // }

    /**
     * Obtener políticas futuras por proyecto
     */
    // public function futurasPorProyecto(string $id_proyecto)
    // {
    //     $politicas = PoliticaPrecioProyecto::where('id_proyecto', $id_proyecto)
    //         ->where('estado', true)
    //         ->whereNotNull('aplica_desde')
    //         ->where('aplica_desde', '>', Carbon::now())
    //         ->orderBy('aplica_desde', 'asc')
    //         ->get();

    //     return response()->json([
    //         'success' => true,
    //         'data' => $politicas
    //     ], 200);
    // }

    /**
     * Obtener resumen de una política
     */
    public function resumen(string $id)
    {
        $politica = PoliticaPrecioProyecto::with([
            'proyecto.ubicacion.ciudad',
            'proyecto.estado_proyecto'
        ])->find($id);

        if (!$politica) {
            return response()->json([
                'success' => false,
                'message' => 'Política de precio no encontrada'
            ], 404);
        }

        $vigencia = 'No aplica';
        if ($politica->aplica_desde) {
            if (Carbon::parse($politica->aplica_desde)->isFuture()) {
                $vigencia = 'Futura (aplica desde ' . $politica->aplica_desde->format('d/m/Y') . ')';
            } else {
                $vigencia = 'Vigente (desde ' . $politica->aplica_desde->format('d/m/Y') . ')';
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id_politica_precio' => $politica->id_politica_precio,
                'proyecto' => [
                    'id' => $politica->proyecto->id_proyecto,
                    'nombre' => $politica->proyecto->nombre,
                    'estado' => $politica->proyecto->estado_proyecto->nombre,
                    'ubicacion' => $politica->proyecto->ubicacion->direccion . ', ' . 
                                  $politica->proyecto->ubicacion->ciudad->nombre
                ],
                'ventas_por_escalon' => $politica->ventas_por_escalon,
                'porcentaje_aumento' => $politica->porcentaje_aumento . '%',
                'aplica_desde' => $politica->aplica_desde ? $politica->aplica_desde->format('d/m/Y') : null,
                'vigencia' => $vigencia,
                'estado' => $politica->estado ? 'Activa' : 'Inactiva'
            ]
        ], 200);
    }

    /**
     * Obtener estadísticas de políticas de precio
     */
    public function estadisticas()
    {
        $totalPoliticas = PoliticaPrecioProyecto::count();
        $politicasActivas = PoliticaPrecioProyecto::where('estado', true)->count();
        $politicasInactivas = PoliticaPrecioProyecto::where('estado', false)->count();
        
        $politicasVigentes = PoliticaPrecioProyecto::where('estado', true)
            ->where(function ($query) {
                $query->whereNull('aplica_desde')
                    ->orWhere('aplica_desde', '<=', Carbon::now());
            })
            ->count();

        $politicasFuturas = PoliticaPrecioProyecto::where('estado', true)
            ->whereNotNull('aplica_desde')
            ->where('aplica_desde', '>', Carbon::now())
            ->count();

        $promedioAumento = PoliticaPrecioProyecto::where('estado', true)
            ->whereNotNull('porcentaje_aumento')
            ->avg('porcentaje_aumento');

        $promedioVentasEscalon = PoliticaPrecioProyecto::where('estado', true)
            ->whereNotNull('ventas_por_escalon')
            ->avg('ventas_por_escalon');

        $politicasPorProyecto = PoliticaPrecioProyecto::selectRaw('id_proyecto, COUNT(*) as total')
            ->with('proyecto:id_proyecto,nombre')
            ->groupBy('id_proyecto')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'proyecto' => $item->proyecto->nombre,
                    'total_politicas' => $item->total
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'total_politicas' => $totalPoliticas,
                'activas' => $politicasActivas,
                'inactivas' => $politicasInactivas,
                'vigentes' => $politicasVigentes,
                'futuras' => $politicasFuturas,
                'promedio_aumento' => round($promedioAumento, 3) . '%',
                'promedio_ventas_escalon' => round($promedioVentasEscalon, 0),
                'top_proyectos_con_politicas' => $politicasPorProyecto
            ]
        ], 200);
    }

    /**
     * Calcular precio con política aplicada
     */
    public function calcularPrecio(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_politica_precio' => 'required|exists:politica_precio_proyecto,id_politica_precio',
            'precio_base' => 'required|numeric|min:0',
            'numero_ventas' => 'required|integer|min:0'
        ], [
            'id_politica_precio.required' => 'La política de precio es obligatoria',
            'id_politica_precio.exists' => 'La política de precio seleccionada no existe',
            'precio_base.required' => 'El precio base es obligatorio',
            'precio_base.numeric' => 'El precio base debe ser un número',
            'precio_base.min' => 'El precio base no puede ser negativo',
            'numero_ventas.required' => 'El número de ventas es obligatorio',
            'numero_ventas.integer' => 'El número de ventas debe ser un entero',
            'numero_ventas.min' => 'El número de ventas no puede ser negativo'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $politica = PoliticaPrecioProyecto::find($request->id_politica_precio);

        if (!$politica->estado) {
            return response()->json([
                'success' => false,
                'message' => 'La política de precio no está activa'
            ], 422);
        }

        $precioBase = $request->precio_base;
        $numeroVentas = $request->numero_ventas;
        $ventasPorEscalon = $politica->ventas_por_escalon ?? 1;
        $porcentajeAumento = $politica->porcentaje_aumento ?? 0;

        // Calcular el escalón actual
        $escalonActual = floor($numeroVentas / $ventasPorEscalon);

        // Calcular el precio con el aumento acumulado
        $precioFinal = $precioBase * pow((1 + ($porcentajeAumento / 100)), $escalonActual);

        // Calcular cuántas ventas faltan para el siguiente escalón
        $ventasParaSiguienteEscalon = (($escalonActual + 1) * $ventasPorEscalon) - $numeroVentas;

        // Calcular el precio del siguiente escalón
        $precioSiguienteEscalon = $precioBase * pow((1 + ($porcentajeAumento / 100)), $escalonActual + 1);

        return response()->json([
            'success' => true,
            'data' => [
                'precio_base' => round($precioBase, 2),
                'numero_ventas' => $numeroVentas,
                'escalon_actual' => $escalonActual,
                'precio_actual' => round($precioFinal, 2),
                'aumento_aplicado' => round((($precioFinal - $precioBase) / $precioBase) * 100, 3) . '%',
                'siguiente_escalon' => [
                    'ventas_faltantes' => $ventasParaSiguienteEscalon,
                    'precio_estimado' => round($precioSiguienteEscalon, 2),
                    'aumento_adicional' => $porcentajeAumento . '%'
                ],
                'politica' => [
                    'ventas_por_escalon' => $ventasPorEscalon,
                    'porcentaje_aumento' => $porcentajeAumento . '%'
                ]
            ]
        ], 200);
    }

    /**
     * Proyección de precios por escalones
     */
    // public function proyeccionPrecios(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'id_politica_precio' => 'required|exists:politica_precio_proyecto,id_politica_precio',
    //         'precio_base' => 'required|numeric|min:0',
    //         'numero_escalones' => 'required|integer|min:1|max:50'
    //     ], [
    //         'id_politica_precio.required' => 'La política de precio es obligatoria',
    //         'id_politica_precio.exists' => 'La política de precio seleccionada no existe',
    //         'precio_base.required' => 'El precio base es obligatorio',
    //         'precio_base.numeric' => 'El precio base debe ser un número',
    //         'precio_base.min' => 'El precio base no puede ser negativo',
    //         'numero_escalones.required' => 'El número de escalones es obligatorio',
    //         'numero_escalones.integer' => 'El número de escalones debe ser un entero',
    //         'numero_escalones.min' => 'Debe proyectar al menos 1 escalón',
    //         'numero_escalones.max' => 'No se pueden proyectar más de 50 escalones'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     $politica = PoliticaPrecioProyecto::with('proyecto')->find($request->id_politica_precio);
    //     $precioBase = $request->precio_base;
    //     $numeroEscalones = $request->numero_escalones;
    //     $ventasPorEscalon = $politica->ventas_por_escalon ?? 1;
    //     $porcentajeAumento = $politica->porcentaje_aumento ?? 0;

    //     $proyeccion = [];
    //     for ($escalon = 0; $escalon <= $numeroEscalones; $escalon++) {
    //         $precioEscalon = $precioBase * pow((1 + ($porcentajeAumento / 100)), $escalon);
    //         $proyeccion[] = [
    //             'escalon' => $escalon,
    //             'ventas_desde' => $escalon * $ventasPorEscalon,
    //             'ventas_hasta' => (($escalon + 1) * $ventasPorEscalon) - 1,
    //             'precio' => round($precioEscalon, 2),
    //             'aumento_acumulado' => round((($precioEscalon - $precioBase) / $precioBase) * 100, 3) . '%'
    //         ];
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'data' => [
    //             'proyecto' => $politica->proyecto->nombre,
    //             'precio_base' => $precioBase,
    //             'ventas_por_escalon' => $ventasPorEscalon,
    //             'porcentaje_aumento' => $porcentajeAumento . '%',
    //             'proyeccion' => $proyeccion
    //         ]
    //     ], 200);
    // }
}