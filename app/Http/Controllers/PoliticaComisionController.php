<?php

namespace App\Http\Controllers;

use App\Models\PoliticaComision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PoliticaComisionController extends Controller
{
    /**
     * Listar todas las políticas de comisión
     */
    public function index()
    {
        $politicas = PoliticaComision::with('proyecto.ubicacion.ciudad')->get();

        return response()->json([
            'success' => true,
            'data' => $politicas
        ], 200);
    }

    /**
     * Crear una nueva política de comisión
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_proyecto' => 'required|exists:proyecto,id_proyecto',
            'aplica_a' => 'nullable|string|max:50',
            'base_calculo' => 'nullable|string|max:50',
            'porcentaje' => 'nullable|numeric|min:0|max:999.999',
            'valor_fijo' => 'nullable|numeric|min:0|max:9999999999999999.99',
            'minimo_venta_estado' => 'nullable|string|max:30',
            'descripcion' => 'nullable|string|max:300',
            'vigente_desde' => 'nullable|date',
            'vigente_hasta' => 'nullable|date|after_or_equal:vigente_desde'
        ], [
            'id_proyecto.required' => 'El proyecto es obligatorio',
            'id_proyecto.exists' => 'El proyecto seleccionado no existe',
            'aplica_a.max' => 'El campo "aplica a" no puede exceder 50 caracteres',
            'base_calculo.max' => 'La base de cálculo no puede exceder 50 caracteres',
            'porcentaje.numeric' => 'El porcentaje debe ser un número',
            'porcentaje.min' => 'El porcentaje no puede ser negativo',
            'porcentaje.max' => 'El porcentaje no puede exceder 999.999',
            'valor_fijo.numeric' => 'El valor fijo debe ser un número',
            'valor_fijo.min' => 'El valor fijo no puede ser negativo',
            'valor_fijo.max' => 'El valor fijo excede el límite permitido',
            'minimo_venta_estado.max' => 'El estado mínimo de venta no puede exceder 30 caracteres',
            'descripcion.max' => 'La descripción no puede exceder 300 caracteres',
            'vigente_desde.date' => 'La fecha de inicio debe ser una fecha válida',
            'vigente_hasta.date' => 'La fecha de fin debe ser una fecha válida',
            'vigente_hasta.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Validar que al menos uno de los dos (porcentaje o valor_fijo) esté presente
        if (!$request->porcentaje && !$request->valor_fijo) {
            return response()->json([
                'success' => false,
                'message' => 'Debe especificar al menos un porcentaje o un valor fijo para la comisión'
            ], 422);
        }

        $politica = PoliticaComision::create($request->all());
        $politica->load('proyecto');

        return response()->json([
            'success' => true,
            'message' => 'Política de comisión creada exitosamente',
            'data' => $politica
        ], 201);
    }

    /**
     * Mostrar una política de comisión específica
     */
    public function show(string $id)
    {
        $politica = PoliticaComision::with([
            'proyecto.ubicacion.ciudad.departamento.pais',
            'proyecto.estado_proyecto'
        ])->find($id);

        if (!$politica) {
            return response()->json([
                'success' => false,
                'message' => 'Política de comisión no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $politica
        ], 200);
    }

    /**
     * Actualizar una política de comisión
     */
    public function update(Request $request, string $id)
    {
        $politica = PoliticaComision::find($id);

        if (!$politica) {
            return response()->json([
                'success' => false,
                'message' => 'Política de comisión no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'id_proyecto' => 'required|exists:proyecto,id_proyecto',
            'aplica_a' => 'nullable|string|max:50',
            'base_calculo' => 'nullable|string|max:50',
            'porcentaje' => 'nullable|numeric|min:0|max:999.999',
            'valor_fijo' => 'nullable|numeric|min:0|max:9999999999999999.99',
            'minimo_venta_estado' => 'nullable|string|max:30',
            'descripcion' => 'nullable|string|max:300',
            'vigente_desde' => 'nullable|date',
            'vigente_hasta' => 'nullable|date|after_or_equal:vigente_desde'
        ], [
            'id_proyecto.required' => 'El proyecto es obligatorio',
            'id_proyecto.exists' => 'El proyecto seleccionado no existe',
            'aplica_a.max' => 'El campo "aplica a" no puede exceder 50 caracteres',
            'base_calculo.max' => 'La base de cálculo no puede exceder 50 caracteres',
            'porcentaje.numeric' => 'El porcentaje debe ser un número',
            'porcentaje.min' => 'El porcentaje no puede ser negativo',
            'porcentaje.max' => 'El porcentaje no puede exceder 999.999',
            'valor_fijo.numeric' => 'El valor fijo debe ser un número',
            'valor_fijo.min' => 'El valor fijo no puede ser negativo',
            'valor_fijo.max' => 'El valor fijo excede el límite permitido',
            'minimo_venta_estado.max' => 'El estado mínimo de venta no puede exceder 30 caracteres',
            'descripcion.max' => 'La descripción no puede exceder 300 caracteres',
            'vigente_desde.date' => 'La fecha de inicio debe ser una fecha válida',
            'vigente_hasta.date' => 'La fecha de fin debe ser una fecha válida',
            'vigente_hasta.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Validar que al menos uno de los dos (porcentaje o valor_fijo) esté presente
        if (!$request->porcentaje && !$request->valor_fijo) {
            return response()->json([
                'success' => false,
                'message' => 'Debe especificar al menos un porcentaje o un valor fijo para la comisión'
            ], 422);
        }

        $politica->update($request->all());
        $politica->load('proyecto');

        return response()->json([
            'success' => true,
            'message' => 'Política de comisión actualizada exitosamente',
            'data' => $politica
        ], 200);
    }

    /**
     * Eliminar una política de comisión
     */
    public function destroy(string $id)
    {
        $politica = PoliticaComision::find($id);

        if (!$politica) {
            return response()->json([
                'success' => false,
                'message' => 'Política de comisión no encontrada'
            ], 404);
        }

        $politica->delete();

        return response()->json([
            'success' => true,
            'message' => 'Política de comisión eliminada exitosamente'
        ], 200);
    }

    /**
     * Listar políticas de comisión por proyecto
     */
    public function byProyecto(string $id_proyecto)
    {
        $politicas = PoliticaComision::where('id_proyecto', $id_proyecto)
            ->orderBy('vigente_desde', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $politicas
        ], 200);
    }

    /**
     * Obtener políticas vigentes
     */
    public function vigentes()
    {
        $hoy = Carbon::now()->format('Y-m-d');

        $politicas = PoliticaComision::where(function ($query) use ($hoy) {
            $query->where(function ($q) use ($hoy) {
                $q->where('vigente_desde', '<=', $hoy)
                  ->where('vigente_hasta', '>=', $hoy);
            })
            ->orWhere(function ($q) use ($hoy) {
                $q->where('vigente_desde', '<=', $hoy)
                  ->whereNull('vigente_hasta');
            })
            ->orWhere(function ($q) {
                $q->whereNull('vigente_desde')
                  ->whereNull('vigente_hasta');
            });
        })
        ->with('proyecto')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $politicas
        ], 200);
    }

    /**
     * Obtener políticas vigentes por proyecto
     */
    public function vigentesPorProyecto(string $id_proyecto)
    {
        $hoy = Carbon::now()->format('Y-m-d');

        $politicas = PoliticaComision::where('id_proyecto', $id_proyecto)
            ->where(function ($query) use ($hoy) {
                $query->where(function ($q) use ($hoy) {
                    $q->where('vigente_desde', '<=', $hoy)
                      ->where('vigente_hasta', '>=', $hoy);
                })
                ->orWhere(function ($q) use ($hoy) {
                    $q->where('vigente_desde', '<=', $hoy)
                      ->whereNull('vigente_hasta');
                })
                ->orWhere(function ($q) {
                    $q->whereNull('vigente_desde')
                      ->whereNull('vigente_hasta');
                });
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => $politicas
        ], 200);
    }

    /**
     * Obtener políticas vencidas
     */
    public function vencidas()
    {
        $hoy = Carbon::now()->format('Y-m-d');

        $politicas = PoliticaComision::where('vigente_hasta', '<', $hoy)
            ->with('proyecto')
            ->orderBy('vigente_hasta', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $politicas
        ], 200);
    }

    /**
     * Obtener políticas por tipo de aplicación
     */
    public function byAplicaA(string $aplica_a)
    {
        $politicas = PoliticaComision::where('aplica_a', 'ILIKE', '%' . $aplica_a . '%')
            ->with('proyecto')
            ->get();

        if ($politicas->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron políticas con ese tipo de aplicación'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $politicas
        ], 200);
    }

    /**
     * Buscar políticas de comisión
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

        $politicas = PoliticaComision::where('descripcion', 'ILIKE', '%' . $request->termino . '%')
            ->orWhere('aplica_a', 'ILIKE', '%' . $request->termino . '%')
            ->orWhere('base_calculo', 'ILIKE', '%' . $request->termino . '%')
            ->with('proyecto')
            ->get();

        if ($politicas->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron políticas con ese término de búsqueda'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $politicas
        ], 200);
    }

    /**
     * Obtener resumen de una política de comisión
     */
    public function resumen(string $id)
    {
        $politica = PoliticaComision::with([
            'proyecto.ubicacion.ciudad',
            'proyecto.estado_proyecto'
        ])->find($id);

        if (!$politica) {
            return response()->json([
                'success' => false,
                'message' => 'Política de comisión no encontrada'
            ], 404);
        }

        $hoy = Carbon::now()->format('Y-m-d');
        $estado = 'Sin vigencia definida';

        if ($politica->vigente_desde && $politica->vigente_hasta) {
            if ($hoy < $politica->vigente_desde->format('Y-m-d')) {
                $estado = 'Próxima a iniciar';
            } elseif ($hoy > $politica->vigente_hasta->format('Y-m-d')) {
                $estado = 'Vencida';
            } else {
                $estado = 'Vigente';
            }
        } elseif ($politica->vigente_desde && !$politica->vigente_hasta) {
            $estado = $hoy >= $politica->vigente_desde->format('Y-m-d') ? 'Vigente' : 'Próxima a iniciar';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id_politica_comision' => $politica->id_politica_comision,
                'proyecto' => [
                    'id' => $politica->proyecto->id_proyecto,
                    'nombre' => $politica->proyecto->nombre,
                    'estado' => $politica->proyecto->estado_proyecto->nombre
                ],
                'aplica_a' => $politica->aplica_a,
                'base_calculo' => $politica->base_calculo,
                'comision' => [
                    'porcentaje' => $politica->porcentaje,
                    'valor_fijo' => $politica->valor_fijo,
                    'tipo' => $politica->porcentaje ? 'Porcentaje' : ($politica->valor_fijo ? 'Valor Fijo' : 'No definido')
                ],
                'minimo_venta_estado' => $politica->minimo_venta_estado,
                'descripcion' => $politica->descripcion,
                'vigencia' => [
                    'desde' => $politica->vigente_desde?->format('Y-m-d'),
                    'hasta' => $politica->vigente_hasta?->format('Y-m-d'),
                    'estado' => $estado
                ]
            ]
        ], 200);
    }

    /**
     * Calcular comisión estimada
     */
    public function calcularComision(Request $request, string $id)
    {
        $politica = PoliticaComision::find($id);

        if (!$politica) {
            return response()->json([
                'success' => false,
                'message' => 'Política de comisión no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'valor_base' => 'required|numeric|min:0'
        ], [
            'valor_base.required' => 'El valor base es obligatorio',
            'valor_base.numeric' => 'El valor base debe ser un número',
            'valor_base.min' => 'El valor base no puede ser negativo'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $comision = 0;
        $tipo_calculo = '';

        if ($politica->porcentaje) {
            $comision = ($request->valor_base * $politica->porcentaje) / 100;
            $tipo_calculo = 'Porcentaje (' . $politica->porcentaje . '%)';
        } elseif ($politica->valor_fijo) {
            $comision = $politica->valor_fijo;
            $tipo_calculo = 'Valor Fijo';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'valor_base' => $request->valor_base,
                'comision_calculada' => round($comision, 2),
                'tipo_calculo' => $tipo_calculo,
                'politica' => [
                    'id' => $politica->id_politica_comision,
                    'descripcion' => $politica->descripcion,
                    'aplica_a' => $politica->aplica_a
                ]
            ]
        ], 200);
    }

    /**
     * Obtener estadísticas de políticas de comisión
     */
    public function estadisticas()
    {
        $hoy = Carbon::now()->format('Y-m-d');

        $totalPoliticas = PoliticaComision::count();
        
        $vigentes = PoliticaComision::where(function ($query) use ($hoy) {
            $query->where(function ($q) use ($hoy) {
                $q->where('vigente_desde', '<=', $hoy)
                  ->where('vigente_hasta', '>=', $hoy);
            })
            ->orWhere(function ($q) use ($hoy) {
                $q->where('vigente_desde', '<=', $hoy)
                  ->whereNull('vigente_hasta');
            })
            ->orWhere(function ($q) {
                $q->whereNull('vigente_desde')
                  ->whereNull('vigente_hasta');
            });
        })->count();

        $vencidas = PoliticaComision::where('vigente_hasta', '<', $hoy)->count();

        $porProyecto = PoliticaComision::selectRaw('id_proyecto, COUNT(*) as total')
            ->with('proyecto:id_proyecto,nombre')
            ->groupBy('id_proyecto')
            ->get()
            ->map(function ($item) {
                return [
                    'proyecto' => $item->proyecto->nombre,
                    'total_politicas' => $item->total
                ];
            });

        $porTipo = [
            'porcentaje' => PoliticaComision::whereNotNull('porcentaje')->count(),
            'valor_fijo' => PoliticaComision::whereNotNull('valor_fijo')->whereNull('porcentaje')->count()
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'total_politicas' => $totalPoliticas,
                'vigentes' => $vigentes,
                'vencidas' => $vencidas,
                'por_proyecto' => $porProyecto,
                'por_tipo_comision' => $porTipo
            ]
        ], 200);
    }

    /**
     * Clonar política de comisión
     */
    // public function clonar(string $id)
    // {
    //     $politicaOriginal = PoliticaComision::find($id);

    //     if (!$politicaOriginal) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Política de comisión no encontrada'
    //         ], 404);
    //     }

    //     $nuevaPolitica = $politicaOriginal->replicate();
    //     $nuevaPolitica->vigente_desde = null;
    //     $nuevaPolitica->vigente_hasta = null;
    //     $nuevaPolitica->save();

    //     $nuevaPolitica->load('proyecto');

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Política de comisión clonada exitosamente',
    //         'data' => $nuevaPolitica
    //     ], 201);
    // }
}