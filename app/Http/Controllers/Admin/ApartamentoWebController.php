<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Apartamento;
use App\Models\EstadoInmueble;
use App\Models\PisoTorre;
use App\Models\Proyecto;
use App\Models\TipoApartamento;
use App\Models\Torre;
use App\Services\PriceEngine;
use App\Support\RedirectBackTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ApartamentoWebController extends Controller
{
    public function index(Request $request)
    {
        $empleado = $request->user()->load('cargo');

        $apartamentos = Apartamento::with([
            'tipoApartamento',
            'torre.proyecto',
            'pisoTorre',
            'estadoInmueble',
        ])
            ->orderBy(
                'id_apartamento',
                'desc'
            )
            ->get()
            ->map(function ($a) {
                return [
                    'id_apartamento' =>
                    $a->id_apartamento,

                    'numero' =>
                    $a->numero,

                    'valor_total' =>
                    $a->valor_final,

                    'tipo' =>
                    $a->tipoApartamento?->nombre,

                    'estado' =>
                    $a->estadoInmueble?->nombre,

                    'proyecto' =>
                    $a->torre?->proyecto?->nombre,

                    'torre' =>
                    $a->torre?->nombre_torre,

                    'piso' =>
                    $a->pisoTorre?->nivel,
                ];
            });

        return Inertia::render(
            'Admin/Apartamento/Index',
            [
                'apartamentos' => $apartamentos,
                'empleado' => $empleado,
            ]
        );
    }

    public function create(Request $request)
    {
        $empleado = $request->user()->load('cargo');

        $proyectos = Proyecto::select(
            'id_proyecto',
            'nombre'
        )
            ->orderBy('nombre')
            ->get();

        /*
         * La configuración de prima viaja con cada tipo.
         */
        $tipos = TipoApartamento::select([
            'id_tipo_apartamento',
            'id_proyecto',
            'nombre',
            'valor_estimado',

            'prima_altura_activa',
            'nivel_inicio_prima',
            'prima_altura_base',
            'prima_altura_incremento',
        ])
            ->orderBy('nombre')
            ->get();

        $estados = EstadoInmueble::select(
            'id_estado_inmueble',
            'nombre'
        )
            ->orderBy('nombre')
            ->get();

        /*
         * Todavía enviamos configuración antigua
         * para tipos legacy con prima_altura_activa = NULL.
         */
        $torres = Torre::with(
            'proyecto:id_proyecto,prima_altura_base,prima_altura_incremento,prima_altura_activa'
        )
            ->select(
                'id_torre',
                'nombre_torre',
                'id_proyecto',
                'nivel_inicio_prima'
            )
            ->orderBy('nombre_torre')
            ->get();

        return Inertia::render(
            'Admin/Apartamento/Create',
            [
                'proyectos' => $proyectos,
                'tipos' => $tipos,
                'estados' => $estados,
                'torres' => $torres,
                'pisos' => [],
                'empleado' => $empleado,
            ]
        );
    }

    public function store(
        Request $request,
        PriceEngine $priceEngine
    ) {
        $isBulk =
            $request->has('apartamentos')
            && is_array(
                $request->input('apartamentos')
            );

        $validated = $request->validate(
            $isBulk
                ? [
                    'id_torre' => [
                        'required',
                        'exists:torres,id_torre',
                    ],

                    'apartamentos' => [
                        'required',
                        'array',
                        'min:1',
                    ],

                    'apartamentos.*.numero' => [
                        'required',
                        'string',
                        'max:20',
                    ],

                    'apartamentos.*.id_tipo_apartamento' => [
                        'required',
                        'exists:tipos_apartamento,id_tipo_apartamento',
                    ],

                    'apartamentos.*.id_piso_torre' => [
                        'required',
                        'exists:pisos_torre,id_piso_torre',
                    ],

                    'apartamentos.*.id_estado_inmueble' => [
                        'required',
                        'exists:estados_inmueble,id_estado_inmueble',
                    ],
                ]
                : [
                    'numero' => [
                        'required',
                        'string',
                        'max:20',
                    ],

                    'id_tipo_apartamento' => [
                        'required',
                        'exists:tipos_apartamento,id_tipo_apartamento',
                    ],

                    'id_torre' => [
                        'required',
                        'exists:torres,id_torre',
                    ],

                    'id_piso_torre' => [
                        'required',
                        'exists:pisos_torre,id_piso_torre',
                    ],

                    'id_estado_inmueble' => [
                        'required',
                        'exists:estados_inmueble,id_estado_inmueble',
                    ],
                ]
        );

        $torre = Torre::with('proyecto')
            ->findOrFail(
                $validated['id_torre']
            );

        $proyecto = $torre->proyecto;

        if (!$proyecto) {
            return back()
                ->withErrors([
                    'id_torre' =>
                    'La torre seleccionada no tiene un proyecto asociado.',
                ])
                ->withInput();
        }

        /*
         * Normalizar filas.
         */
        $rows = $isBulk
            ? collect(
                $validated['apartamentos']
            )->map(function ($row) {
                return [
                    'numero' =>
                    trim(
                        (string) (
                            $row['numero'] ?? ''
                        )
                    ),

                    'id_tipo_apartamento' =>
                    $row['id_tipo_apartamento'] ?? null,

                    'id_piso_torre' =>
                    $row['id_piso_torre'] ?? null,

                    'id_estado_inmueble' =>
                    $row['id_estado_inmueble'] ?? null,
                ];
            })->values()
            : collect([
                [
                    'numero' =>
                    trim(
                        (string) $validated['numero']
                    ),

                    'id_tipo_apartamento' =>
                    $validated['id_tipo_apartamento'],

                    'id_piso_torre' =>
                    $validated['id_piso_torre'],

                    'id_estado_inmueble' =>
                    $validated['id_estado_inmueble'],
                ],
            ]);

        /*
         * ========================================================
         * Duplicados dentro del request
         * ========================================================
         */
        $duplicados =
            $rows
            ->pluck('numero')
            ->duplicates();

        if ($duplicados->isNotEmpty()) {
            $errores = [];

            foreach ($rows as $index => $row) {
                if (
                    $duplicados->contains(
                        $row['numero']
                    )
                ) {
                    $key = $isBulk
                        ? "apartamentos.$index.numero"
                        : 'numero';

                    $errores[$key] =
                        "Número repetido en el formulario: {$row['numero']}";
                }
            }

            return back()
                ->withErrors($errores)
                ->withInput();
        }

        /*
         * ========================================================
         * Pisos
         * ========================================================
         */
        $pisoIds =
            $rows
            ->pluck('id_piso_torre')
            ->unique()
            ->values()
            ->all();

        $pisos = PisoTorre::whereIn(
            'id_piso_torre',
            $pisoIds
        )
            ->get()
            ->keyBy('id_piso_torre');

        /*
         * ========================================================
         * Tipos
         * ========================================================
         */
        $tipoIds =
            $rows
            ->pluck('id_tipo_apartamento')
            ->unique()
            ->values()
            ->all();

        $tipos = TipoApartamento::whereIn(
            'id_tipo_apartamento',
            $tipoIds
        )
            ->select(
                'id_tipo_apartamento',
                'id_proyecto',
                'valor_estimado'
            )
            ->get()
            ->keyBy('id_tipo_apartamento');

        /*
         * ========================================================
         * Integridad Torre / Piso / Tipo / Proyecto
         * ========================================================
         */
        $errores = [];

        foreach ($rows as $index => $row) {
            $piso =
                $pisos->get(
                    $row['id_piso_torre']
                );

            if (
                !$piso
                || (int) $piso->id_torre
                !==
                (int) $torre->id_torre
            ) {
                $key = $isBulk
                    ? "apartamentos.$index.id_piso_torre"
                    : 'id_piso_torre';

                $errores[$key] =
                    'El piso seleccionado no pertenece a la torre indicada.';
            }

            $tipo =
                $tipos->get(
                    $row['id_tipo_apartamento']
                );

            if (
                !$tipo
                || (int) $tipo->id_proyecto
                !==
                (int) $proyecto->id_proyecto
            ) {
                $key = $isBulk
                    ? "apartamentos.$index.id_tipo_apartamento"
                    : 'id_tipo_apartamento';

                $errores[$key] =
                    'El tipo de apartamento seleccionado no pertenece al proyecto de la torre.';
            }
        }

        if (!empty($errores)) {
            return back()
                ->withErrors($errores)
                ->withInput();
        }

        /*
         * ========================================================
         * Unicidad en BD
         * ========================================================
         */
        $numeros =
            $rows
            ->pluck('numero')
            ->all();

        $existentes =
            Apartamento::where(
                'id_torre',
                $torre->id_torre
            )
            ->whereIn(
                'numero',
                $numeros
            )
            ->pluck('numero')
            ->all();

        if (!empty($existentes)) {
            $errores = [];

            foreach ($rows as $index => $row) {
                if (
                    in_array(
                        $row['numero'],
                        $existentes,
                        true
                    )
                ) {
                    $key = $isBulk
                        ? "apartamentos.$index.numero"
                        : 'numero';

                    $errores[$key] =
                        'Ya existe un apartamento con este número en la torre seleccionada.';
                }
            }

            return back()
                ->withErrors($errores)
                ->withInput();
        }

        /*
         * ========================================================
         * Crear apartamentos
         * ========================================================
         */
        DB::transaction(function () use (
            $rows,
            $torre,
            $tipos,
            $priceEngine
        ) {
            foreach ($rows as $row) {
                $tipo =
                    $tipos->get(
                        $row['id_tipo_apartamento']
                    );

                $valorBase =
                    (float) (
                        $tipo->valor_estimado ?? 0
                    );

                /*
                 * Creamos solamente con la información base.
                 *
                 * PriceEngine será responsable de:
                 *
                 * - prima_altura
                 * - valor_politica
                 * - valor_final
                 */
                $apartamento =
                    Apartamento::create([
                        'numero' =>
                        $row['numero'],

                        'id_tipo_apartamento' =>
                        $row['id_tipo_apartamento'],

                        'id_torre' =>
                        $torre->id_torre,

                        'id_piso_torre' =>
                        $row['id_piso_torre'],

                        'id_estado_inmueble' =>
                        $row['id_estado_inmueble'],

                        'valor_total' =>
                        $valorBase,

                        'prima_altura' =>
                        0,

                        'valor_politica' =>
                        0,

                        'valor_final' =>
                        $valorBase,
                    ]);

                /*
                 * ÚNICA fuente de verdad de precio.
                 */
                $priceEngine
                    ->recalcularApartamentoSegunPoliticasActivas(
                        $apartamento
                    );
            }
        });

        return RedirectBackTo::respond(
            $request,
            'locales.create',
            [],
            $isBulk
                ? 'Apartamentos creados exitosamente'
                : 'Apartamento creado exitosamente'
        );
    }

    public function show(
        Request $request,
        $id
    ) {
        $empleado = $request->user()->load('cargo');

        $apartamento = Apartamento::with([
            'tipoApartamento',
            'torre.proyecto.ubicacion.ciudad',
            'pisoTorre',
            'estadoInmueble',
            'parqueaderos',
        ])->findOrFail($id);

        $totalParqueaderos =
            $apartamento
            ->parqueaderos
            ->count();

        $parqVehiculo =
            $apartamento
            ->parqueaderos
            ->where(
                'tipo',
                'Vehiculo'
            )
            ->count();

        $parqMoto =
            $apartamento
            ->parqueaderos
            ->where(
                'tipo',
                'Moto'
            )
            ->count();

        return Inertia::render(
            'Admin/Apartamento/Show',
            [
                'apartamento' => $apartamento,

                'resumen' => [
                    'id_apartamento' =>
                    $apartamento->id_apartamento,

                    'numero' =>
                    $apartamento->numero,

                    'tipo' =>
                    $apartamento
                        ->tipoApartamento
                        ?->nombre,

                    'torre' =>
                    $apartamento
                        ->torre
                        ?->nombre_torre,

                    'piso' =>
                    $apartamento
                        ->pisoTorre
                        ?->nivel,

                    'proyecto' =>
                    $apartamento
                        ->torre
                        ?->proyecto
                        ?->nombre,

                    'ubicacion' =>
                    optional(
                        $apartamento
                            ->torre
                            ?->proyecto
                            ?->ubicacion,
                        function ($u) {
                            $ciudad =
                                $u->ciudad?->nombre
                                ?? '';

                            return trim(
                                ($u->direccion ?? '')
                                    .
                                    (
                                        strlen($ciudad)
                                        ? ', ' . $ciudad
                                        : ''
                                    )
                            );
                        }
                    ),

                    'estado' =>
                    $apartamento
                        ->estadoInmueble
                        ?->nombre,

                    'valor_total' =>
                    $apartamento->valor_total,

                    'parqueaderos' => [
                        'total' =>
                        $totalParqueaderos,

                        'vehiculos' =>
                        $parqVehiculo,

                        'motos' =>
                        $parqMoto,
                    ],
                ],

                'empleado' => $empleado,
            ]
        );
    }

    public function edit(
        Request $request,
        $id
    ) {
        $empleado = $request->user()->load('cargo');

        $a = Apartamento::with([
            'torre.proyecto',
            'pisoTorre',
        ])->findOrFail($id);

        $proyectos = Proyecto::select(
            'id_proyecto',
            'nombre'
        )
            ->orderBy('nombre')
            ->get();

        /*
         * Incluimos configuración de prima.
         */
        $tipos = TipoApartamento::select([
            'id_tipo_apartamento',
            'id_proyecto',
            'nombre',
            'valor_estimado',

            'prima_altura_activa',
            'nivel_inicio_prima',
            'prima_altura_base',
            'prima_altura_incremento',
        ])
            ->orderBy('nombre')
            ->get();

        $estados = EstadoInmueble::select(
            'id_estado_inmueble',
            'nombre'
        )
            ->orderBy('nombre')
            ->get();

        $idProyecto =
            $a->torre?->id_proyecto;

        /*
         * Configuración legacy todavía disponible
         * para tipos no migrados.
         */
        $torres = Torre::with(
            'proyecto:id_proyecto,nombre,prima_altura_base,prima_altura_incremento,prima_altura_activa'
        )
            ->where(
                'id_proyecto',
                $idProyecto
            )
            ->select(
                'id_torre',
                'nombre_torre',
                'id_proyecto',
                'nivel_inicio_prima'
            )
            ->orderBy('nombre_torre')
            ->get();

        $pisos = PisoTorre::where(
            'id_torre',
            $a->id_torre
        )
            ->select(
                'id_piso_torre',
                'nivel',
                'id_torre'
            )
            ->orderBy('nivel')
            ->get();

        return Inertia::render(
            'Admin/Apartamento/Edit',
            [
                'apartamento' => [
                    'id_apartamento' =>
                    $a->id_apartamento,

                    'numero' =>
                    $a->numero,

                    'id_tipo_apartamento' =>
                    $a->id_tipo_apartamento,

                    'id_torre' =>
                    $a->id_torre,

                    'id_piso_torre' =>
                    $a->id_piso_torre,

                    'id_estado_inmueble' =>
                    $a->id_estado_inmueble,

                    'valor_total' =>
                    $a->valor_total,

                    'id_proyecto' =>
                    $idProyecto,

                    'prima_altura' =>
                    $a->prima_altura,

                    'valor_politica' =>
                    $a->valor_politica,

                    'valor_final' =>
                    $a->valor_final,
                ],

                'proyectos' => $proyectos,
                'tipos' => $tipos,
                'estados' => $estados,
                'torres' => $torres,
                'pisos' => $pisos,
                'empleado' => $empleado,
            ]
        );
    }

    public function update(
        Request $request,
        $id,
        PriceEngine $priceEngine
    ) {
        $apartamento =
            Apartamento::findOrFail($id);

        $validated = $request->validate(
            [
                'numero' => [
                    'required',
                    'string',
                    'max:20',
                ],

                'id_tipo_apartamento' => [
                    'required',
                    'exists:tipos_apartamento,id_tipo_apartamento',
                ],

                'id_torre' => [
                    'required',
                    'exists:torres,id_torre',
                ],

                'id_piso_torre' => [
                    'required',
                    'exists:pisos_torre,id_piso_torre',
                ],

                'id_estado_inmueble' => [
                    'required',
                    'exists:estados_inmueble,id_estado_inmueble',
                ],
            ],
            [
                'numero.required' =>
                'El número del apartamento es obligatorio.',

                'numero.max' =>
                'El número del apartamento no puede exceder 20 caracteres.',

                'id_tipo_apartamento.required' =>
                'El tipo de apartamento es obligatorio.',

                'id_tipo_apartamento.exists' =>
                'El tipo de apartamento seleccionado no existe.',

                'id_torre.required' =>
                'La torre es obligatoria.',

                'id_torre.exists' =>
                'La torre seleccionada no existe.',

                'id_piso_torre.required' =>
                'El piso es obligatorio.',

                'id_piso_torre.exists' =>
                'El piso seleccionado no existe.',

                'id_estado_inmueble.required' =>
                'El estado del inmueble es obligatorio.',

                'id_estado_inmueble.exists' =>
                'El estado del inmueble seleccionado no existe.',
            ]
        );

        $torre = Torre::with('proyecto')
            ->findOrFail(
                $validated['id_torre']
            );

        $proyecto = $torre->proyecto;

        if (!$proyecto) {
            return back()
                ->withErrors([
                    'id_torre' =>
                    'La torre seleccionada no tiene un proyecto asociado.',
                ])
                ->withInput();
        }

        /*
         * Validar piso.
         */
        $piso = PisoTorre::findOrFail(
            $validated['id_piso_torre']
        );

        if (
            (int) $piso->id_torre
            !==
            (int) $torre->id_torre
        ) {
            return back()
                ->withErrors([
                    'id_piso_torre' =>
                    'El piso seleccionado no pertenece a la torre indicada.',
                ])
                ->withInput();
        }

        /*
         * Validar tipo.
         */
        $tipo = TipoApartamento::select(
            'id_tipo_apartamento',
            'id_proyecto',
            'valor_estimado'
        )->findOrFail(
            $validated['id_tipo_apartamento']
        );

        if (
            (int) $tipo->id_proyecto
            !==
            (int) $proyecto->id_proyecto
        ) {
            return back()
                ->withErrors([
                    'id_tipo_apartamento' =>
                    'El tipo de apartamento seleccionado no pertenece al proyecto de la torre.',
                ])
                ->withInput();
        }

        /*
         * Unicidad número + torre.
         */
        $exists = Apartamento::where(
            'numero',
            $validated['numero']
        )
            ->where(
                'id_torre',
                $validated['id_torre']
            )
            ->where(
                'id_apartamento',
                '!=',
                $apartamento->id_apartamento
            )
            ->exists();

        if ($exists) {
            return back()
                ->withErrors([
                    'numero' =>
                    'Ya existe otro apartamento con este número en la torre seleccionada.',
                ])
                ->withInput();
        }

        $valorBase =
            (float) (
                $tipo->valor_estimado ?? 0
            );

        DB::transaction(function () use (
            $apartamento,
            $validated,
            $valorBase,
            $priceEngine
        ) {
            /*
             * saveQuietly porque hacemos UN único
             * recálculo explícito después.
             */
            $apartamento->fill([
                'numero' =>
                trim(
                    (string) $validated['numero']
                ),

                'id_tipo_apartamento' =>
                $validated['id_tipo_apartamento'],

                'id_torre' =>
                $validated['id_torre'],

                'id_piso_torre' =>
                $validated['id_piso_torre'],

                'id_estado_inmueble' =>
                $validated['id_estado_inmueble'],

                'valor_total' =>
                $valorBase,
            ]);

            $apartamento->saveQuietly();

            /*
             * Prima y política se recalculan aquí.
             */
            $priceEngine
                ->recalcularApartamentoSegunPoliticasActivas(
                    $apartamento->fresh()
                );
        });

        return redirect()
            ->route('apartamentos.index')
            ->with(
                'success',
                'Apartamento actualizado exitosamente'
            );
    }

    public function destroy($id)
    {
        $apartamento =
            Apartamento::withCount('parqueaderos')
            ->findOrFail($id);

        if ($apartamento->parqueaderos_count > 0) {
            return back()->withErrors([
                'delete' =>
                'No se puede eliminar el apartamento porque tiene parqueaderos asociados.',
            ]);
        }

        $apartamento->delete();

        return redirect()
            ->route('apartamentos.index')
            ->with(
                'success',
                'Apartamento eliminado exitosamente'
            );
    }

    /* ============================================================
     * Selects dependientes
     * ============================================================ */

    public function torresPorProyecto(
        $id_proyecto
    ) {
        /*
         * Conservamos datos legacy exclusivamente
         * durante la transición.
         */
        return Torre::with(
            'proyecto:id_proyecto,prima_altura_base,prima_altura_incremento,prima_altura_activa'
        )
            ->where(
                'id_proyecto',
                $id_proyecto
            )
            ->select(
                'id_torre',
                'nombre_torre',
                'id_proyecto',
                'nivel_inicio_prima'
            )
            ->orderBy('nombre_torre')
            ->get();
    }

    public function pisosPorTorre(
        $id_torre
    ) {
        return PisoTorre::where(
            'id_torre',
            $id_torre
        )
            ->select(
                'id_piso_torre',
                'nivel',
                'id_torre'
            )
            ->orderBy('nivel')
            ->get();
    }
}
