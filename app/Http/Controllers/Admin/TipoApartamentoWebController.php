<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proyecto;
use App\Models\TipoApartamento;
use App\Services\PriceEngine;
use App\Support\RedirectBackTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class TipoApartamentoWebController extends Controller
{
    public function index(Request $request)
    {
        $empleado = $request->user()->load('cargo');

        $tipos = TipoApartamento::with(['proyecto'])
            ->withCount('apartamentos')
            ->orderBy('id_tipo_apartamento', 'desc')
            ->get()
            ->map(fn($t) => [
                'id_tipo_apartamento' => $t->id_tipo_apartamento,
                'nombre' => $t->nombre,
                'area_construida' => $t->area_construida,
                'area_privada' => $t->area_privada,
                'cantidad_habitaciones' => $t->cantidad_habitaciones,
                'cantidad_banos' => $t->cantidad_banos,
                'valor_m2' => $t->valor_m2,
                'valor_estimado' => $t->valor_estimado,

                'prima_altura_activa' => $t->prima_altura_activa,
                'nivel_inicio_prima' => $t->nivel_inicio_prima,
                'prima_altura_base' => $t->prima_altura_base,
                'prima_altura_incremento' => $t->prima_altura_incremento,

                'apartamentos_count' => $t->apartamentos_count,
                'proyecto' => $t->proyecto?->nombre,
            ]);

        return Inertia::render('Admin/TipoApartamento/Index', [
            'tipos' => $tipos,
            'empleado' => $empleado,
        ]);
    }

    public function create(Request $request)
    {
        $empleado = $request->user()->load('cargo');

        $proyectos = Proyecto::select('id_proyecto', 'nombre')
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Admin/TipoApartamento/Create', [
            'proyectos' => $proyectos,
            'empleado' => $empleado,
        ]);
    }

    public function store(Request $request)
    {
        /*
         * ========================================================
         * CREACIÓN MASIVA
         * ========================================================
         */
        if (
            $request->has('tipos')
            && is_array($request->input('tipos'))
        ) {
            $validator = Validator::make(
                $request->all(),
                [
                    'id_proyecto' => [
                        'required',
                        'exists:proyectos,id_proyecto',
                    ],

                    'tipos' => [
                        'required',
                        'array',
                        'min:1',
                    ],

                    'tipos.*.nombre' => [
                        'required',
                        'string',
                        'max:100',
                    ],

                    'tipos.*.area_construida' => [
                        'nullable',
                        'numeric',
                        'min:0',
                        'max:99999999.99',
                    ],

                    'tipos.*.area_privada' => [
                        'nullable',
                        'numeric',
                        'min:0',
                        'max:99999999.99',
                    ],

                    'tipos.*.cantidad_habitaciones' => [
                        'nullable',
                        'integer',
                        'min:0',
                        'max:32767',
                    ],

                    'tipos.*.cantidad_banos' => [
                        'nullable',
                        'integer',
                        'min:0',
                        'max:32767',
                    ],

                    'tipos.*.valor_m2' => [
                        'nullable',
                        'numeric',
                        'min:0',
                        'max:9999999999999999.99',
                    ],

                    'tipos.*.imagen' => [
                        'nullable',
                        'image',
                        'mimes:jpg,jpeg,png,webp',
                        'max:2048',
                    ],

                    /*
                     * Prima de altura
                     */
                    'tipos.*.prima_altura_activa' => [
                        'nullable',
                        'boolean',
                    ],

                    'tipos.*.nivel_inicio_prima' => [
                        'nullable',
                        'integer',
                        'min:1',
                        'max:32767',
                    ],

                    'tipos.*.prima_altura_base' => [
                        'nullable',
                        'numeric',
                        'min:0',
                        'max:9999999999999999.99',
                    ],

                    'tipos.*.prima_altura_incremento' => [
                        'nullable',
                        'numeric',
                        'min:0',
                        'max:9999999999999999.99',
                    ],
                ],
                [
                    'id_proyecto.required' => 'El proyecto es obligatorio.',
                    'id_proyecto.exists' => 'El proyecto seleccionado no existe.',

                    'tipos.*.nombre.required' => 'El nombre del tipo es obligatorio.',

                    'tipos.*.nivel_inicio_prima.integer' => 'El nivel de inicio de prima debe ser un número entero.',
                    'tipos.*.nivel_inicio_prima.min' => 'El nivel de inicio de prima debe ser mínimo 1.',

                    'tipos.*.prima_altura_base.numeric' => 'La prima base debe ser un valor numérico.',
                    'tipos.*.prima_altura_base.min' => 'La prima base no puede ser negativa.',

                    'tipos.*.prima_altura_incremento.numeric' => 'El incremento de prima debe ser un valor numérico.',
                    'tipos.*.prima_altura_incremento.min' => 'El incremento de prima no puede ser negativo.',
                ]
            );

            /*
             * Los campos son obligatorios solamente cuando
             * la prima está activa.
             */
            $validator->after(function ($validator) use ($request) {
                foreach (
                    (array) $request->input('tipos', [])
                    as $index => $row
                ) {
                    $this->validarConfiguracionPrima(
                        $validator,
                        $row,
                        "tipos.$index"
                    );
                }
            });

            if ($validator->fails()) {
                return back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $validated = $validator->validated();

            $tiposFiles = $request->file('tipos') ?? [];

            DB::transaction(function () use (
                $validated,
                $tiposFiles
            ) {
                foreach (
                    $validated['tipos']
                    as $index => $row
                ) {
                    /*
                     * Configuración nueva.
                     *
                     * Los tipos creados desde ahora quedan
                     * explícitamente configurados.
                     */
                    $row = $this->normalizarConfiguracionPrima(
                        $row,
                        true
                    );

                    /*
                     * Imagen
                     */
                    if (!empty($tiposFiles[$index]['imagen'])) {
                        $row['imagen'] = $tiposFiles[$index]['imagen']
                            ->store(
                                'tipos-apartamento',
                                'public'
                            );
                    }

                    /*
                     * Valor estimado.
                     *
                     * Mantiene el comportamiento actual del
                     * formulario masivo:
                     *
                     * área construida × valor m²
                     */
                    $row['valor_estimado'] =
                        $this->calcularValorEstimado(
                            $row,
                            1
                        );

                    $row['id_proyecto'] =
                        $validated['id_proyecto'];

                    unset(
                        $row['_key'],
                        $row['_fileName']
                    );

                    TipoApartamento::create($row);
                }
            });

            return RedirectBackTo::respond(
                $request,
                'admin.apartamentos.create',
                [],
                'Tipos de apartamento creados exitosamente'
            );
        }

        /*
         * ========================================================
         * CREACIÓN SIMPLE
         * ========================================================
         */

        $validator = Validator::make(
            $request->all(),
            [
                'id_proyecto' => [
                    'required',
                    'exists:proyectos,id_proyecto',
                ],

                'nombre' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'area_construida' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:99999999.99',
                ],

                'area_privada' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:99999999.99',
                ],

                'cantidad_habitaciones' => [
                    'nullable',
                    'integer',
                    'min:0',
                    'max:32767',
                ],

                'cantidad_banos' => [
                    'nullable',
                    'integer',
                    'min:0',
                    'max:32767',
                ],

                'valor_m2' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:9999999999999999.99',
                ],

                'imagen' => [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:2048',
                ],

                'prima_altura_activa' => [
                    'nullable',
                    'boolean',
                ],

                'nivel_inicio_prima' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:32767',
                ],

                'prima_altura_base' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:9999999999999999.99',
                ],

                'prima_altura_incremento' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:9999999999999999.99',
                ],
            ],
            [
                'nombre.required' => 'El nombre del tipo de apartamento es obligatorio.',
                'nombre.max' => 'El nombre no puede exceder 100 caracteres.',

                'area_construida.numeric' => 'El área construida debe ser un valor numérico.',
                'area_construida.min' => 'El área construida no puede ser negativa.',

                'area_privada.numeric' => 'El área privada debe ser un valor numérico.',
                'area_privada.min' => 'El área privada no puede ser negativa.',

                'cantidad_habitaciones.integer' => 'La cantidad de habitaciones debe ser un número entero.',
                'cantidad_habitaciones.min' => 'La cantidad de habitaciones no puede ser negativa.',

                'cantidad_banos.integer' => 'La cantidad de baños debe ser un número entero.',
                'cantidad_banos.min' => 'La cantidad de baños no puede ser negativa.',

                'valor_m2.numeric' => 'El valor por m² debe ser un valor numérico.',
                'valor_m2.min' => 'El valor por m² no puede ser negativo.',

                'imagen.image' => 'El archivo debe ser una imagen válida.',
                'imagen.mimes' => 'La imagen debe ser JPG, PNG o WEBP.',
                'imagen.max' => 'La imagen no puede pesar más de 2MB.',

                'nivel_inicio_prima.integer' => 'El nivel de inicio de prima debe ser un número entero.',
                'nivel_inicio_prima.min' => 'El nivel de inicio debe ser mínimo 1.',

                'prima_altura_base.numeric' => 'La prima base debe ser un valor numérico.',
                'prima_altura_base.min' => 'La prima base no puede ser negativa.',

                'prima_altura_incremento.numeric' => 'El incremento debe ser un valor numérico.',
                'prima_altura_incremento.min' => 'El incremento no puede ser negativo.',
            ]
        );

        $validator->after(function ($validator) use ($request) {
            $this->validarConfiguracionPrima(
                $validator,
                $request->all()
            );
        });

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();

        $validated = $this->normalizarConfiguracionPrima(
            $validated,
            true
        );

        if ($request->hasFile('imagen')) {
            $validated['imagen'] =
                $request->file('imagen')
                ->store(
                    'tipos-apartamento',
                    'public'
                );
        }

        /*
         * Se conserva el comportamiento que ya tenía
         * tu creación SIMPLE: factor 1.08.
         *
         * Más adelante podemos eliminar esta inconsistencia
         * respecto al modo masivo si así lo decides.
         */
        $validated['valor_estimado'] =
            $this->calcularValorEstimado(
                $validated,
                1.08
            );

        $tipo = TipoApartamento::create($validated);

        return RedirectBackTo::respond(
            $request,
            'tipos-apartamento.show',
            [$tipo->id_tipo_apartamento],
            'Tipo de apartamento creado exitosamente',
            [
                'id_tipo_apartamento' =>
                $tipo->id_tipo_apartamento,
            ]
        );
    }

    public function show(
        Request $request,
        $id
    ) {
        $empleado = $request->user()->load('cargo');

        $tipo = TipoApartamento::with([
            'proyecto',
            'apartamentos.torre',
            'apartamentos.estadoInmueble',
        ])->findOrFail($id);

        return Inertia::render(
            'Admin/TipoApartamento/Show',
            [
                'tipo' => [
                    'id_proyecto' =>
                    $tipo->proyecto?->nombre,

                    'id_tipo_apartamento' =>
                    $tipo->id_tipo_apartamento,

                    'nombre' =>
                    $tipo->nombre,

                    'area_construida' =>
                    $tipo->area_construida,

                    'area_privada' =>
                    $tipo->area_privada,

                    'cantidad_habitaciones' =>
                    $tipo->cantidad_habitaciones,

                    'cantidad_banos' =>
                    $tipo->cantidad_banos,

                    'valor_m2' =>
                    $tipo->valor_m2,

                    'valor_estimado' =>
                    $tipo->valor_estimado,

                    'imagen' =>
                    $tipo->imagen,

                    'prima_altura_activa' =>
                    $tipo->prima_altura_activa,

                    'nivel_inicio_prima' =>
                    $tipo->nivel_inicio_prima,

                    'prima_altura_base' =>
                    $tipo->prima_altura_base,

                    'prima_altura_incremento' =>
                    $tipo->prima_altura_incremento,
                ],

                'apartamentos' =>
                $tipo->apartamentos
                    ->map(function ($a) {
                        return [
                            'id_apartamento' =>
                            $a->id_apartamento,

                            'numero' =>
                            $a->numero,

                            'torre' =>
                            $a->torre?->nombre_torre,

                            'estado' =>
                            $a->estadoInmueble?->nombre,
                        ];
                    }),

                'empleado' => $empleado,
            ]
        );
    }

    public function edit(
        Request $request,
        $id
    ) {
        $empleado = $request->user()->load('cargo');

        $t = TipoApartamento::findOrFail($id);

        $proyectos = Proyecto::select(
            'id_proyecto',
            'nombre'
        )
            ->orderBy('nombre')
            ->get();

        return Inertia::render(
            'Admin/TipoApartamento/Edit',
            [
                'tipo' => [
                    'id_proyecto' =>
                    $t->id_proyecto,

                    'id_tipo_apartamento' =>
                    $t->id_tipo_apartamento,

                    'nombre' =>
                    $t->nombre,

                    'area_construida' =>
                    $t->area_construida,

                    'area_privada' =>
                    $t->area_privada,

                    'cantidad_habitaciones' =>
                    $t->cantidad_habitaciones,

                    'cantidad_banos' =>
                    $t->cantidad_banos,

                    'valor_m2' =>
                    $t->valor_m2,

                    'imagen' =>
                    $t->imagen,

                    /*
                     * NULL significa que todavía usa
                     * Proyecto + Torre.
                     */
                    'prima_altura_activa' =>
                    $t->prima_altura_activa,

                    'nivel_inicio_prima' =>
                    $t->nivel_inicio_prima,

                    'prima_altura_base' =>
                    $t->prima_altura_base,

                    'prima_altura_incremento' =>
                    $t->prima_altura_incremento,
                ],

                'proyectos' => $proyectos,
                'empleado' => $empleado,
            ]
        );
    }

    public function update(
        Request $request,
        $id,
        PriceEngine $priceEngine
    ) {
        $t = TipoApartamento::findOrFail($id);

        $validator = Validator::make(
            $request->all(),
            [
                'id_proyecto' => [
                    'required',
                    'exists:proyectos,id_proyecto',
                ],

                'nombre' => [
                    'required',
                    'string',
                    'max:100',

                    Rule::unique(
                        'tipos_apartamento',
                        'nombre'
                    )
                        ->where(
                            fn($query) =>
                            $query->where(
                                'id_proyecto',
                                $request->id_proyecto
                            )
                        )
                        ->ignore(
                            $t->id_tipo_apartamento,
                            'id_tipo_apartamento'
                        ),
                ],

                'area_construida' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:99999999.99',
                ],

                'area_privada' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:99999999.99',
                ],

                'cantidad_habitaciones' => [
                    'nullable',
                    'integer',
                    'min:0',
                    'max:32767',
                ],

                'cantidad_banos' => [
                    'nullable',
                    'integer',
                    'min:0',
                    'max:32767',
                ],

                'valor_m2' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:9999999999999999.99',
                ],

                'imagen' => [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:2048',
                ],

                /*
                 * Prima
                 */
                'prima_altura_activa' => [
                    'nullable',
                    'boolean',
                ],

                'nivel_inicio_prima' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:32767',
                ],

                'prima_altura_base' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:9999999999999999.99',
                ],

                'prima_altura_incremento' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:9999999999999999.99',
                ],
            ],
            [
                'nombre.required' =>
                'El nombre del tipo de apartamento es obligatorio.',

                'nombre.max' =>
                'El nombre no puede exceder 100 caracteres.',

                'area_construida.numeric' =>
                'El área construida debe ser un valor numérico.',

                'area_construida.min' =>
                'El área construida no puede ser negativa.',

                'area_privada.numeric' =>
                'El área privada debe ser un valor numérico.',

                'area_privada.min' =>
                'El área privada no puede ser negativa.',

                'cantidad_habitaciones.integer' =>
                'La cantidad de habitaciones debe ser un número entero.',

                'cantidad_habitaciones.min' =>
                'La cantidad de habitaciones no puede ser negativa.',

                'cantidad_banos.integer' =>
                'La cantidad de baños debe ser un número entero.',

                'cantidad_banos.min' =>
                'La cantidad de baños no puede ser negativa.',

                'valor_m2.numeric' =>
                'El valor por m² debe ser un valor numérico.',

                'valor_m2.min' =>
                'El valor por m² no puede ser negativo.',

                'imagen.image' =>
                'El archivo debe ser una imagen válida.',

                'imagen.mimes' =>
                'La imagen debe ser JPG, PNG o WEBP.',

                'imagen.max' =>
                'La imagen no puede pesar más de 2MB.',

                'nivel_inicio_prima.integer' =>
                'El nivel de inicio de prima debe ser un número entero.',

                'nivel_inicio_prima.min' =>
                'El nivel de inicio debe ser mínimo 1.',

                'prima_altura_base.numeric' =>
                'La prima base debe ser un valor numérico.',

                'prima_altura_base.min' =>
                'La prima base no puede ser negativa.',

                'prima_altura_incremento.numeric' =>
                'El incremento debe ser un valor numérico.',

                'prima_altura_incremento.min' =>
                'El incremento no puede ser negativo.',
            ]
        );

        $validator->after(function ($validator) use ($request) {
            $this->validarConfiguracionPrima(
                $validator,
                $request->all()
            );
        });

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();

        /*
         * Si por algún consumidor antiguo no llega este
         * campo, conserva el estado actual.
         */
        if (!array_key_exists(
            'prima_altura_activa',
            $validated
        )) {
            $validated['prima_altura_activa'] =
                $t->prima_altura_activa;
        }

        $validated =
            $this->normalizarConfiguracionPrima(
                $validated,
                false
            );

        if ($request->hasFile('imagen')) {
            if ($t->imagen) {
                Storage::disk('public')
                    ->delete($t->imagen);
            }

            $validated['imagen'] =
                $request->file('imagen')
                ->store(
                    'tipos-apartamento',
                    'public'
                );
        }

        /*
         * Mantiene el comportamiento actual de update:
         *
         * área construida × valor m²
         */
        $validated['valor_estimado'] =
            $this->calcularValorEstimado(
                $validated,
                1
            );

        DB::transaction(function () use (
            $t,
            $validated,
            $priceEngine
        ) {
            $t->update($validated);

            /*
             * Recalcula apartamentos DISPONIBLES
             * asociados a este tipo.
             */
            $priceEngine->recalcularTipoApartamento(
                $t->fresh()
            );
        });

        return redirect()
            ->route(
                'tipos-apartamento.show',
                $t->id_tipo_apartamento
            )
            ->with(
                'success',
                'Tipo de apartamento actualizado exitosamente'
            );
    }

    public function destroy($id)
    {
        $t = TipoApartamento::withCount('apartamentos')
            ->findOrFail($id);

        if ($t->apartamentos_count > 0) {
            return back()->withErrors([
                'delete' =>
                'No se puede eliminar el tipo de apartamento porque tiene apartamentos asociados.',
            ]);
        }

        $t->delete();

        return redirect()
            ->route('tipos-apartamento.index')
            ->with(
                'success',
                'Tipo de apartamento eliminado exitosamente'
            );
    }

    /* ============================================================
     * Validación condicional de prima
     * ============================================================ */
    private function validarConfiguracionPrima(
        $validator,
        array $data,
        string $prefix = ''
    ): void {
        $activaRaw =
            $data['prima_altura_activa'] ?? null;

        /*
         * NULL corresponde a configuración legacy.
         */
        if (
            $activaRaw === null
            || $activaRaw === ''
        ) {
            return;
        }

        $activa = filter_var(
            $activaRaw,
            FILTER_VALIDATE_BOOLEAN
        );

        if (!$activa) {
            return;
        }

        $campos = [
            'nivel_inicio_prima' =>
            'El nivel de inicio de prima es obligatorio cuando la prima está activa.',

            'prima_altura_base' =>
            'La prima base es obligatoria cuando la prima está activa.',

            'prima_altura_incremento' =>
            'El incremento de prima es obligatorio cuando la prima está activa.',
        ];

        foreach ($campos as $campo => $mensaje) {
            $valor = $data[$campo] ?? null;

            if ($valor === null || $valor === '') {
                $key = $prefix
                    ? "{$prefix}.{$campo}"
                    : $campo;

                $validator->errors()
                    ->add(
                        $key,
                        $mensaje
                    );
            }
        }
    }

    /* ============================================================
     * Normalizar configuración
     * ============================================================ */
    private function normalizarConfiguracionPrima(
        array $data,
        bool $esNuevo
    ): array {
        if (!array_key_exists(
            'prima_altura_activa',
            $data
        )) {
            /*
             * Todo tipo NUEVO usa explícitamente
             * la nueva configuración.
             */
            if ($esNuevo) {
                $data['prima_altura_activa'] = false;
            }

            return $data;
        }

        if (
            $data['prima_altura_activa'] === null
            || $data['prima_altura_activa'] === ''
        ) {
            $data['prima_altura_activa'] = null;

            return $data;
        }

        $data['prima_altura_activa'] =
            filter_var(
                $data['prima_altura_activa'],
                FILTER_VALIDATE_BOOLEAN
            );

        return $data;
    }

    /* ============================================================
     * Cálculo valor estimado
     * ============================================================ */
    private function calcularValorEstimado(
        array $data,
        float $factor = 1
    ): ?int {
        $area =
            (float) ($data['area_construida'] ?? 0);

        $valorM2 =
            (float) ($data['valor_m2'] ?? 0);

        if ($area <= 0 || $valorM2 <= 0) {
            return null;
        }

        return (int) ceil(
            $area
                * $valorM2
                * $factor
        );
    }
}
