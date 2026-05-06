<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proyecto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use App\Models\Estado;
use App\Models\Ubicacion;
use App\Models\Torre;
use App\Models\ZonaSocial;

class ProyectoController extends Controller
{
    public function index(Request $request)
    {
        $empleado = $request->user()->load('cargo');
        // Paginación con relaciones para la tabla
        $proyectos = Proyecto::with([
            'estado_proyecto',
            'ubicacion.ciudad.departamento.pais',
            'torres',
            'zonasSociales'
        ])->paginate(10);

        return Inertia::render('Admin/Proyectos/Index', [
            'proyectos' => $proyectos,
            'filters' => $request->all('search', 'page'),
            'empleado' => $empleado,
        ]);
    }

    // ✅ NUEVO: activar / desactivar
    public function toggleActivo(Request $request, Proyecto $proyecto)
    {
        // Si quieres restringir por rol, aquí es el lugar (policy/gate).
        $proyecto->activo = !$proyecto->activo;
        $proyecto->save();

        return back()->with('success', $proyecto->activo ? 'Proyecto activado.' : 'Proyecto desactivado.');
    }

    public function create(Request $request)
    {
        return inertia('Admin/Proyectos/Create', [
            'empleado' => $request->user()->load('cargo'),
            'estados' => Estado::all(),
            'ubicaciones' => Ubicacion::with('ciudad.departamento.pais')->get(),
            'estadosInmueble' => Estado::all(),
            'proyecto' => null, // importante para wizard nuevo
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:150',
            'logo_proyecto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'descripcion' => 'nullable|string|max:500',
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
            'id_estado' => 'required|exists:estados,id_estado',
            'id_ubicacion' => 'required|exists:ubicaciones,id_ubicacion',
            'plazo_max_separacion_dias' => 'nullable|integer|min:1|max:3650',
            'activo' => 'nullable|boolean',
            'planes_pago' => 'nullable|array|max:10',
            'planes_pago.*.id_plan_pago_proyecto' => 'nullable|integer|exists:planes_pago_proyecto,id_plan_pago_proyecto',
            'planes_pago.*.codigo' => 'required_with:planes_pago|string|max:30',
            'planes_pago.*.nombre' => 'required_with:planes_pago|string|max:120',
            'planes_pago.*.descripcion' => 'nullable|string|max:1000',
            'planes_pago.*.orden' => 'nullable|integer|min:1|max:10',
            'planes_pago.*.tipo_plan' => [
                'required_with:planes_pago',
                Rule::in([
                    'cuota_inicial_mensual',
                    'cuota_inicial_contado',
                    'pago_total_diferido',
                    'especial_manual',
                ]),
            ],
            'planes_pago.*.valor_separacion' => 'nullable|numeric|min:0|max:9999999999999999.99',
            'planes_pago.*.porcentaje_cuota_inicial' => 'nullable|numeric|min:0|max:100',
            'planes_pago.*.plazo_cuota_inicial_meses' => 'nullable|integer|min:1|max:32767',
            'planes_pago.*.frecuencia_cuota_inicial_meses' => 'nullable|integer|min:1|max:32767',
            'planes_pago.*.plazo_pago_total_dias' => 'nullable|integer|min:1|max:3650',
            'planes_pago.*.porcentaje_escritura' => 'nullable|numeric|min:0|max:100',
            'planes_pago.*.tipo_descuento' => [
                'nullable',
                Rule::in(['ninguno', 'valor_fijo', 'porcentaje']),
            ],
            'planes_pago.*.valor_descuento' => 'nullable|numeric|min:0|max:9999999999999999.99',
            'planes_pago.*.base_descuento' => [
                'nullable',
                Rule::in(['ninguna', 'precio_total', 'cuota_inicial']),
            ],
            'planes_pago.*.beneficio_comercial' => 'nullable|string|max:1000',
            'planes_pago.*.permite_plazo_manual' => 'nullable',
            'planes_pago.*.permite_cuotas_manuales' => 'nullable',
            'planes_pago.*.activo' => 'nullable',
        ], [
            'nombre.required' => 'El nombre del proyecto es obligatorio',
            'id_estado.required' => 'El estado del proyecto es obligatorio',
            'id_ubicacion.required' => 'La ubicación del proyecto es obligatoria',
            'logo_proyecto.image' => 'El archivo debe ser una imagen válida.',
            'logo_proyecto.mimes' => 'El logo debe estar en formato JPG, JPEG, PNG o WEBP.',
            'logo_proyecto.max' => 'El logo no puede pesar más de 2 MB.',
        ]);

        $validator->after(function ($validator) use ($request) {
            $this->validarReglasPlanesPago($validator, $request->input('planes_pago', []));
        });

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        // $data = $request->all();
        // if (!array_key_exists('activo', $data) || $data['activo'] === null) {
        //     $data['activo'] = true; // default
        // }

        // $proyecto = Proyecto::create($data);

        // $data = $validator->validated();

        // unset($data['logo_proyecto']);

        // if (!array_key_exists('activo', $data) || $data['activo'] === null) {
        //     $data['activo'] = true;
        // }

        // if ($request->hasFile('logo_proyecto')) {
        //     $data['logo_path'] = $request->file('logo_proyecto')->store('proyectos/logos', 'public');
        // }

        // $proyecto = Proyecto::create($data);

        $data = $validator->validated();

        $planesPago = $data['planes_pago'] ?? [];

        unset($data['planes_pago'], $data['logo_proyecto']);

        if (!array_key_exists('activo', $data) || $data['activo'] === null) {
            $data['activo'] = true;
        }

        $proyecto = DB::transaction(function () use ($request, $data, $planesPago) {
            $dataProyecto = $data;

            if ($request->hasFile('logo_proyecto')) {
                $dataProyecto['logo_path'] = $request->file('logo_proyecto')
                    ->store('proyectos/logos', 'public');
            }

            $proyecto = Proyecto::create($dataProyecto);

            $this->sincronizarPlanesPago($proyecto, $planesPago);

            return $proyecto;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'id_proyecto' => $proyecto->id_proyecto,
                'message' => 'Proyecto creado exitosamente',
            ]);
        }

        return redirect()->route('proyectos.index')->with('success', 'Proyecto creado exitosamente');
    }

    public function show(Request $request, $id_proyecto)
    {
        $empleado = $request->user()->load('cargo');
        $tab = request()->get('tab', 'detalle');
        $search = request()->get('search');

        // $proyecto = Proyecto::with(['ubicacion.ciudad', 'estado_proyecto', 'politicasPrecio' => function ($query) {
        //     $query->orderBy('aplica_desde', 'desc');
        // }])->findOrFail($id_proyecto);
        $proyecto = Proyecto::with([
            'ubicacion.ciudad',
            'estado_proyecto',
            'planesPago' => function ($query) {
                $query->orderBy('orden')->orderBy('id_plan_pago_proyecto');
            },
            'politicasPrecio' => function ($query) {
                $query->orderBy('aplica_desde', 'desc');
            },
        ])->findOrFail($id_proyecto);

        $torres = Torre::with(['estado'])
            ->where('id_proyecto', $id_proyecto)
            ->when($search, fn($q) => $q->where('nombre_torre', 'ILIKE', '%' . $search . '%'))
            ->orderBy('id_torre', 'desc')
            ->paginate(10)
            ->withQueryString();

        return \Inertia\Inertia::render('Admin/Proyectos/Show', [
            'proyecto' => $proyecto,
            'tab' => $tab,
            'torres' => $torres,
            'filters' => [
                'search' => $search,
            ],
            'empleado' => $empleado,
        ]);
    }

    // public function edit(Proyecto $proyecto, Request $request)
    // {
    //     $empleado = $request->user()->load('cargo');
    //     $estados = Estado::all();
    //     $ubicaciones = Ubicacion::with('ciudad')->get();

    //     return Inertia::render('Admin/Proyectos/Edit', compact('proyecto', 'estados', 'ubicaciones', 'empleado'));
    // }

    public function edit(Proyecto $proyecto, Request $request)
    {
        $empleado = $request->user()->load('cargo');
        $estados = Estado::all();
        $ubicaciones = Ubicacion::with('ciudad')->get();

        $proyecto->load([
            'planesPago' => function ($query) {
                $query->orderBy('orden')->orderBy('id_plan_pago_proyecto');
            },
        ]);

        return Inertia::render('Admin/Proyectos/Edit', compact('proyecto', 'estados', 'ubicaciones', 'empleado'));
    }

    public function update(Request $request, Proyecto $proyecto)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:150',
            'logo_proyecto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'logo_proyecto_eliminar' => 'nullable|boolean',
            'descripcion' => 'nullable|string|max:500',
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
            'id_estado' => 'required|exists:estados,id_estado',
            'id_ubicacion' => 'required|exists:ubicaciones,id_ubicacion',
            'plazo_max_separacion_dias' => 'nullable|integer|min:1|max:3650',
            'activo' => 'nullable|boolean',
            'planes_pago' => 'nullable|array|max:10',
            'planes_pago.*.id_plan_pago_proyecto' => 'nullable|integer|exists:planes_pago_proyecto,id_plan_pago_proyecto',
            'planes_pago.*.codigo' => 'required_with:planes_pago|string|max:30',
            'planes_pago.*.nombre' => 'required_with:planes_pago|string|max:120',
            'planes_pago.*.descripcion' => 'nullable|string|max:1000',
            'planes_pago.*.orden' => 'nullable|integer|min:1|max:10',
            'planes_pago.*.tipo_plan' => [
                'required_with:planes_pago',
                Rule::in([
                    'cuota_inicial_mensual',
                    'cuota_inicial_contado',
                    'pago_total_diferido',
                    'especial_manual',
                ]),
            ],
            'planes_pago.*.valor_separacion' => 'nullable|numeric|min:0|max:9999999999999999.99',
            'planes_pago.*.porcentaje_cuota_inicial' => 'nullable|numeric|min:0|max:100',
            'planes_pago.*.plazo_cuota_inicial_meses' => 'nullable|integer|min:1|max:32767',
            'planes_pago.*.frecuencia_cuota_inicial_meses' => 'nullable|integer|min:1|max:32767',
            'planes_pago.*.plazo_pago_total_dias' => 'nullable|integer|min:1|max:3650',
            'planes_pago.*.porcentaje_escritura' => 'nullable|numeric|min:0|max:100',
            'planes_pago.*.tipo_descuento' => [
                'nullable',
                Rule::in(['ninguno', 'valor_fijo', 'porcentaje']),
            ],
            'planes_pago.*.valor_descuento' => 'nullable|numeric|min:0|max:9999999999999999.99',
            'planes_pago.*.base_descuento' => [
                'nullable',
                Rule::in(['ninguna', 'precio_total', 'cuota_inicial']),
            ],
            'planes_pago.*.beneficio_comercial' => 'nullable|string|max:1000',
            'planes_pago.*.permite_plazo_manual' => 'nullable',
            'planes_pago.*.permite_cuotas_manuales' => 'nullable',
            'planes_pago.*.activo' => 'nullable',
        ], [
            'nombre.required' => 'El nombre del proyecto es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 150 caracteres',
            'descripcion.max' => 'La descripción no puede exceder 500 caracteres',
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
            'id_ubicacion.exists' => 'La ubicación seleccionada no existe',
            'plazo_max_separacion_dias.integer' => 'El plazo máximo de separación debe ser un número entero.',
            'plazo_max_separacion_dias.min' => 'El plazo debe ser mínimo de 1 día.',
            'plazo_max_separacion_dias.max' => 'El plazo no puede superar los 3650 días.',
            'logo_proyecto.image' => 'El archivo debe ser una imagen válida.',
            'logo_proyecto.mimes' => 'El logo debe estar en formato JPG, JPEG, PNG o WEBP.',
            'logo_proyecto.max' => 'El logo no puede pesar más de 2 MB.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // $proyecto->update($request->all());
        // $proyecto->load(['estado_proyecto', 'ubicacion.ciudad.departamento.pais']);

        // $data = $validator->validated();

        // $eliminarLogo = (bool) ($data['logo_proyecto_eliminar'] ?? false);

        // unset($data['logo_proyecto'], $data['logo_proyecto_eliminar']);

        // if ($eliminarLogo && $proyecto->logo_path) {
        //     Storage::disk('public')->delete($proyecto->logo_path);
        //     $data['logo_path'] = null;
        // }

        // if ($request->hasFile('logo_proyecto')) {
        //     if ($proyecto->logo_path) {
        //         Storage::disk('public')->delete($proyecto->logo_path);
        //     }

        //     $data['logo_path'] = $request->file('logo_proyecto')->store('proyectos/logos', 'public');
        // }

        // $proyecto->update($data);

        $data = $validator->validated();

        $planesPago = $data['planes_pago'] ?? [];

        $eliminarLogo = (bool) ($data['logo_proyecto_eliminar'] ?? false);

        unset(
            $data['planes_pago'],
            $data['logo_proyecto'],
            $data['logo_proyecto_eliminar']
        );

        DB::transaction(function () use ($request, $proyecto, $data, $planesPago, $eliminarLogo) {
            $dataProyecto = $data;

            if ($eliminarLogo && $proyecto->logo_path) {
                Storage::disk('public')->delete($proyecto->logo_path);
                $dataProyecto['logo_path'] = null;
            }

            if ($request->hasFile('logo_proyecto')) {
                if ($proyecto->logo_path) {
                    Storage::disk('public')->delete($proyecto->logo_path);
                }

                $dataProyecto['logo_path'] = $request->file('logo_proyecto')
                    ->store('proyectos/logos', 'public');
            }

            $proyecto->update($dataProyecto);

            if ($request->has('planes_pago')) {
                $this->sincronizarPlanesPago($proyecto, $planesPago);
            }
        });

        $proyecto->load(['estado_proyecto', 'ubicacion.ciudad.departamento.pais']);

        return redirect()->route('proyectos.show', $proyecto)->with('success', 'Proyecto actualizado exitosamente');
    }

    public function destroy(Proyecto $proyecto)
    {
        if ($proyecto->torres()->count() > 0) {
            return back()->with('error', 'No se puede eliminar el proyecto porque tiene torres asociadas.');
        }

        $proyecto->delete();

        return redirect()->route('proyectos.index')->with('success', 'Proyecto eliminado exitosamente');
    }

    private function validarReglasPlanesPago($validator, array $planes): void
    {
        if (empty($planes)) {
            return;
        }

        $codigos = [];

        foreach ($planes as $index => $plan) {
            $prefix = "planes_pago.$index";

            $codigo = strtoupper(trim((string) ($plan['codigo'] ?? '')));
            $nombre = trim((string) ($plan['nombre'] ?? ''));
            $tipoPlan = $plan['tipo_plan'] ?? null;

            if ($codigo !== '') {
                if (in_array($codigo, $codigos, true)) {
                    $validator->errors()->add("$prefix.codigo", 'El código del plan está repetido en este proyecto.');
                }

                $codigos[] = $codigo;
            }

            if ($nombre === '') {
                $validator->errors()->add("$prefix.nombre", 'El nombre del plan es obligatorio.');
            }

            $porcentajeCuotaInicial = $this->numeroNullable($plan['porcentaje_cuota_inicial'] ?? null);
            $porcentajeEscritura = $this->numeroNullable($plan['porcentaje_escritura'] ?? null);
            $plazoCuotaInicial = $this->numeroNullable($plan['plazo_cuota_inicial_meses'] ?? null);
            $frecuenciaCuotaInicial = $this->numeroNullable($plan['frecuencia_cuota_inicial_meses'] ?? null);
            $plazoPagoTotalDias = $this->numeroNullable($plan['plazo_pago_total_dias'] ?? null);

            if ($tipoPlan === 'cuota_inicial_mensual') {
                if ($porcentajeCuotaInicial === null || $porcentajeCuotaInicial <= 0) {
                    $validator->errors()->add("$prefix.porcentaje_cuota_inicial", 'El porcentaje de cuota inicial es obligatorio para este tipo de plan.');
                }

                if ($plazoCuotaInicial === null || $plazoCuotaInicial <= 0) {
                    $validator->errors()->add("$prefix.plazo_cuota_inicial_meses", 'El plazo de cuota inicial es obligatorio para este tipo de plan.');
                }

                if ($frecuenciaCuotaInicial === null || $frecuenciaCuotaInicial <= 0) {
                    $validator->errors()->add("$prefix.frecuencia_cuota_inicial_meses", 'La frecuencia de cuota inicial es obligatoria.');
                }
            }

            if ($tipoPlan === 'cuota_inicial_contado') {
                if ($porcentajeCuotaInicial === null || $porcentajeCuotaInicial <= 0) {
                    $validator->errors()->add("$prefix.porcentaje_cuota_inicial", 'El porcentaje de cuota inicial contado es obligatorio.');
                }
            }

            if ($tipoPlan === 'pago_total_diferido') {
                if ($plazoPagoTotalDias === null || $plazoPagoTotalDias <= 0) {
                    $validator->errors()->add("$prefix.plazo_pago_total_dias", 'El plazo para pagar el saldo total es obligatorio.');
                }
            }

            if ($tipoPlan === 'especial_manual') {
                if ($porcentajeCuotaInicial === null || $porcentajeCuotaInicial <= 0) {
                    $validator->errors()->add("$prefix.porcentaje_cuota_inicial", 'El porcentaje de cuota inicial es obligatorio para el plan especial.');
                }
            }

            if (
                $tipoPlan !== 'pago_total_diferido'
                && $porcentajeCuotaInicial !== null
                && $porcentajeEscritura !== null
                && ($porcentajeCuotaInicial + $porcentajeEscritura) > 100
            ) {
                $validator->errors()->add("$prefix.porcentaje_escritura", 'La suma de cuota inicial y escritura no puede superar el 100%.');
            }

            $tipoDescuento = $plan['tipo_descuento'] ?? 'ninguno';
            $baseDescuento = $plan['base_descuento'] ?? 'ninguna';
            $valorDescuento = $this->numeroNullable($plan['valor_descuento'] ?? null);

            if ($tipoDescuento !== 'ninguno') {
                if ($valorDescuento === null || $valorDescuento <= 0) {
                    $validator->errors()->add("$prefix.valor_descuento", 'El valor del descuento es obligatorio cuando el plan tiene descuento.');
                }

                if ($baseDescuento === 'ninguna') {
                    $validator->errors()->add("$prefix.base_descuento", 'Debes seleccionar la base sobre la cual aplica el descuento.');
                }

                if ($tipoDescuento === 'porcentaje' && $valorDescuento > 100) {
                    $validator->errors()->add("$prefix.valor_descuento", 'El descuento porcentual no puede superar el 100%.');
                }

                if ($baseDescuento === 'cuota_inicial' && $tipoPlan === 'pago_total_diferido') {
                    $validator->errors()->add("$prefix.base_descuento", 'Un pago total diferido no debe usar descuento sobre cuota inicial.');
                }
            }
        }
    }

    private function sincronizarPlanesPago(Proyecto $proyecto, array $planes): void
    {
        $planesNormalizados = $this->normalizarPlanesPago($planes);
        $idsVigentes = [];

        foreach ($planesNormalizados as $plan) {
            $idPlan = $plan['id_plan_pago_proyecto'] ?? null;

            unset($plan['id_plan_pago_proyecto']);

            if ($idPlan) {
                $planExistente = $proyecto->planesPago()
                    ->where('id_plan_pago_proyecto', $idPlan)
                    ->first();

                if ($planExistente) {
                    $planExistente->update($plan);
                    $idsVigentes[] = $planExistente->id_plan_pago_proyecto;
                    continue;
                }
            }

            $nuevoPlan = $proyecto->planesPago()->create($plan);
            $idsVigentes[] = $nuevoPlan->id_plan_pago_proyecto;
        }

        $proyecto->planesPago()
            ->when(!empty($idsVigentes), function ($query) use ($idsVigentes) {
                $query->whereNotIn('id_plan_pago_proyecto', $idsVigentes);
            })
            ->delete();
    }

    private function normalizarPlanesPago(array $planes): array
    {
        return collect($planes)
            ->values()
            ->map(function ($plan, $index) {
                $tipoPlan = $plan['tipo_plan'] ?? 'cuota_inicial_mensual';
                $tipoDescuento = $plan['tipo_descuento'] ?? 'ninguno';

                return [
                    'id_plan_pago_proyecto' => $plan['id_plan_pago_proyecto'] ?? null,
                    'codigo' => strtoupper(trim((string) ($plan['codigo'] ?? ''))),
                    'nombre' => trim((string) ($plan['nombre'] ?? '')),
                    'descripcion' => $this->valorNullable($plan['descripcion'] ?? null),
                    'orden' => (int) ($plan['orden'] ?? ($index + 1)),
                    'tipo_plan' => $tipoPlan,

                    'valor_separacion' => $this->numeroONull($plan['valor_separacion'] ?? 0) ?? 0,
                    'porcentaje_cuota_inicial' => $this->numeroONull($plan['porcentaje_cuota_inicial'] ?? null),
                    'plazo_cuota_inicial_meses' => $this->numeroONull($plan['plazo_cuota_inicial_meses'] ?? null),
                    'frecuencia_cuota_inicial_meses' => $this->numeroONull($plan['frecuencia_cuota_inicial_meses'] ?? 1) ?? 1,
                    'plazo_pago_total_dias' => $this->numeroONull($plan['plazo_pago_total_dias'] ?? null),
                    'porcentaje_escritura' => $this->numeroONull($plan['porcentaje_escritura'] ?? 0) ?? 0,

                    'tipo_descuento' => $tipoDescuento,
                    'valor_descuento' => $tipoDescuento === 'ninguno'
                        ? null
                        : $this->numeroONull($plan['valor_descuento'] ?? null),
                    'base_descuento' => $tipoDescuento === 'ninguno'
                        ? 'ninguna'
                        : ($plan['base_descuento'] ?? 'ninguna'),

                    'beneficio_comercial' => $this->valorNullable($plan['beneficio_comercial'] ?? null),

                    'permite_plazo_manual' => $tipoPlan === 'especial_manual'
                        ? true
                        : $this->booleano($plan['permite_plazo_manual'] ?? false),

                    'permite_cuotas_manuales' => $tipoPlan === 'especial_manual'
                        ? true
                        : $this->booleano($plan['permite_cuotas_manuales'] ?? false),

                    'activo' => $this->booleano($plan['activo'] ?? true),
                ];
            })
            ->filter(function ($plan) {
                return $plan['codigo'] !== '' || $plan['nombre'] !== '';
            })
            ->values()
            ->all();
    }

    private function valorNullable($valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        return trim((string) $valor);
    }

    private function numeroONull($valor)
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        return $valor;
    }

    private function numeroNullable($valor): ?float
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        return is_numeric($valor) ? (float) $valor : null;
    }

    private function booleano($valor): bool
    {
        return filter_var($valor, FILTER_VALIDATE_BOOLEAN);
    }
}
