<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Apartamento;
use App\Models\Proyecto;
use App\Models\Torre;
use App\Models\PisoTorre;
use App\Models\TipoApartamento;
use App\Models\EstadoInmueble;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Support\RedirectBackTo;
use Illuminate\Validation\Rule;

class ApartamentoWebController extends Controller
{
    public function index(Request $request)
    {
        $empleado = $request->user()->load('cargo');
        $apartamentos = Apartamento::with(['tipoApartamento', 'torre.proyecto', 'pisoTorre', 'estadoInmueble'])
            ->orderBy('id_apartamento', 'desc')
            ->get()
            ->map(function ($a) {
                return [
                    'id_apartamento' => $a->id_apartamento,
                    'numero' => $a->numero,
                    'valor_total' => $a->valor_final,
                    'tipo' => $a->tipoApartamento?->nombre,
                    'estado' => $a->estadoInmueble?->nombre,
                    'proyecto' => $a->torre?->proyecto?->nombre,
                    'torre' => $a->torre?->nombre_torre,
                    'piso' => $a->pisoTorre?->nivel,
                ];
            });

        return Inertia::render('Admin/Apartamento/Index', [
            'apartamentos' => $apartamentos,
            'empleado' => $empleado,
        ]);
    }

    public function create(Request $request)
    {
        $empleado = $request->user()->load('cargo');
        // Selects iniciales
        $proyectos = Proyecto::select('id_proyecto', 'nombre')->orderBy('nombre')->get();
        $tipos = TipoApartamento::select('id_tipo_apartamento', 'id_proyecto', 'nombre', 'valor_estimado')->get();
        $estados = EstadoInmueble::select('id_estado_inmueble', 'nombre')->orderBy('nombre')->get();
        $torres = Torre::with('proyecto:id_proyecto,nombre,prima_altura_base,prima_altura_incremento,prima_altura_activa')
            ->select('id_torre', 'nombre_torre', 'id_proyecto')
            ->orderBy('nombre_torre')
            ->get();

        // $pisos = PisoTorre::select('id_piso_torre', 'nivel', 'id_torre')
        //     ->orderBy('nivel')
        //     ->get();

        $pisos = [];

        return Inertia::render('Admin/Apartamento/Create', [
            'proyectos' => $proyectos,
            'tipos' => $tipos,
            'estados' => $estados,
            'torres' => $torres,
            'pisos' => $pisos,
            'empleado' => $empleado,
        ]);
    }

    public function store(Request $request)
    {
        $isBulk = $request->has('apartamentos');

        $validated = $request->validate(
            $isBulk
                ? [
                    'id_torre' => ['required', 'exists:torres,id_torre'],
                    'apartamentos' => ['required', 'array', 'min:1'],

                    'apartamentos.*.numero' => ['required', 'string', 'max:20'],
                    'apartamentos.*.id_tipo_apartamento' => ['required', 'exists:tipos_apartamento,id_tipo_apartamento'],
                    'apartamentos.*.id_piso_torre' => ['required', 'exists:pisos_torre,id_piso_torre'],
                    'apartamentos.*.id_estado_inmueble' => ['required', 'exists:estados_inmueble,id_estado_inmueble'],
                ]
                : [
                    // compatibilidad con creación antigua (si aún existe alguna vista vieja)
                    'numero' => ['required', 'string', 'max:20'],
                    'id_tipo_apartamento' => ['required', 'exists:tipos_apartamento,id_tipo_apartamento'],
                    'id_torre' => ['required', 'exists:torres,id_torre'],
                    'id_piso_torre' => ['required', 'exists:pisos_torre,id_piso_torre'],
                    'id_estado_inmueble' => ['required', 'exists:estados_inmueble,id_estado_inmueble'],
                ]
        );

        $torre = Torre::with('proyecto')->findOrFail($validated['id_torre']);
        $proyecto = $torre->proyecto;

        // Normalizar filas
        $rows = $isBulk
            ? collect($validated['apartamentos'])->map(function ($r) {
                return [
                    'numero' => trim((string)($r['numero'] ?? '')),
                    'id_tipo_apartamento' => $r['id_tipo_apartamento'] ?? null,
                    'id_piso_torre' => $r['id_piso_torre'] ?? null,
                    'id_estado_inmueble' => $r['id_estado_inmueble'] ?? null,
                ];
            })->values()
            : collect([[
                'numero' => trim((string)$validated['numero']),
                'id_tipo_apartamento' => $validated['id_tipo_apartamento'],
                'id_piso_torre' => $validated['id_piso_torre'],
                'id_estado_inmueble' => $validated['id_estado_inmueble'],
            ]]);

        // Duplicados en request
        $dups = $rows->pluck('numero')->duplicates();
        if ($dups->isNotEmpty()) {
            $errs = [];
            foreach ($rows as $i => $r) {
                if ($dups->contains($r['numero'])) {
                    $errs["apartamentos.$i.numero"] = "Número repetido en el formulario: {$r['numero']}";
                }
            }
            return back()->withErrors($errs)->withInput();
        }

        // Validar que todos los pisos pertenezcan a la torre seleccionada
        $pisoIds = $rows->pluck('id_piso_torre')->unique()->values()->all();
        $pisos = PisoTorre::whereIn('id_piso_torre', $pisoIds)->get()->keyBy('id_piso_torre');

        $errs = [];
        foreach ($rows as $i => $r) {
            $piso = $pisos->get($r['id_piso_torre']);
            if (!$piso || (int)$piso->id_torre !== (int)$validated['id_torre']) {
                $errs["apartamentos.$i.id_piso_torre"] = 'El piso seleccionado no pertenece a la torre indicada';
            }
        }
        if (!empty($errs)) return back()->withErrors($errs)->withInput();

        // Unicidad en BD (numero dentro de torre)
        $numeros = $rows->pluck('numero')->all();
        $existentes = Apartamento::where('id_torre', $validated['id_torre'])
            ->whereIn('numero', $numeros)
            ->pluck('numero')
            ->all();

        if (!empty($existentes)) {
            $errs = [];
            foreach ($rows as $i => $r) {
                if (in_array($r['numero'], $existentes, true)) {
                    $key = $isBulk ? "apartamentos.$i.numero" : "numero";
                    $errs[$key] = 'Ya existe un apartamento con este número en la torre seleccionada';
                }
            }
            return back()->withErrors($errs)->withInput();
        }

        // Cargar tipos (valor_estimado) para cálculo
        $tipoIds = $rows->pluck('id_tipo_apartamento')->unique()->values()->all();
        $tipos = TipoApartamento::whereIn('id_tipo_apartamento', $tipoIds)
            ->select('id_tipo_apartamento', 'valor_estimado')
            ->get()
            ->keyBy('id_tipo_apartamento');

        DB::transaction(function () use ($rows, $validated, $proyecto, $tipos, $pisos) {
            foreach ($rows as $r) {
                $tipo = $tipos->get($r['id_tipo_apartamento']);
                $valorBase = (float)($tipo->valor_estimado ?? 0);

                // prima altura por fila (depende del piso)
                $primaAltura = $this->calcularPrimaAltura($r['id_piso_torre'], $validated['id_torre']);

                // política por fila (sobre valor base del tipo)
                $politicaCalc = $this->calcularValorConPolitica($valorBase, $proyecto->id_proyecto);

                Apartamento::create([
                    'numero' => $r['numero'],
                    'id_tipo_apartamento' => $r['id_tipo_apartamento'],
                    'id_torre' => $validated['id_torre'],
                    'id_piso_torre' => $r['id_piso_torre'],
                    'id_estado_inmueble' => $r['id_estado_inmueble'],

                    'prima_altura' => $primaAltura,
                    'valor_total' => $valorBase,
                    'valor_politica' => $politicaCalc['valor_politica'],
                    'valor_final' => $politicaCalc['valor_final'] + $primaAltura,
                ]);
            }
        });

        return RedirectBackTo::respond(
            $request,
            'apartamentos.index',
            [],
            $isBulk ? 'Apartamentos creados exitosamente' : 'Apartamento creado exitosamente'
        );
    }

    public function show(Request $request, $id)
    {
        $empleado = $request->user()->load('cargo');
        $apartamento = Apartamento::with([
            'tipoApartamento',
            'torre.proyecto.ubicacion.ciudad',
            'pisoTorre',
            'estadoInmueble',
            'parqueaderos'
        ])->findOrFail($id);

        // Resumen similar al API
        $totalParqueaderos = $apartamento->parqueaderos->count();
        $parqVehiculo = $apartamento->parqueaderos->where('tipo', 'Vehiculo')->count();
        $parqMoto = $apartamento->parqueaderos->where('tipo', 'Moto')->count();

        return Inertia::render('Admin/Apartamento/Show', [
            'apartamento' => $apartamento,
            'resumen' => [
                'id_apartamento' => $apartamento->id_apartamento,
                'numero' => $apartamento->numero,
                'tipo' => $apartamento->tipoApartamento?->nombre,
                'torre' => $apartamento->torre?->nombre_torre,
                'piso' => $apartamento->pisoTorre?->nivel,
                'proyecto' => $apartamento->torre?->proyecto?->nombre,
                'ubicacion' => optional($apartamento->torre?->proyecto?->ubicacion, function ($u) {
                    $ciudad = $u->ciudad?->nombre ?? '';
                    return trim(($u->direccion ?? '') . (strlen($ciudad) ? ', ' . $ciudad : ''));
                }),
                'estado' => $apartamento->estadoInmueble?->nombre,
                'valor_total' => $apartamento->valor_total,
                'parqueaderos' => [
                    'total' => $totalParqueaderos,
                    'vehiculos' => $parqVehiculo,
                    'motos' => $parqMoto,
                ],
                'empleado' => $empleado,
            ]
        ]);
    }

    public function edit(Request $request, $id)
    {
        $empleado = $request->user()->load('cargo');
        $a = Apartamento::with(['torre.proyecto', 'pisoTorre'])->findOrFail($id);

        $proyectos = Proyecto::select('id_proyecto', 'nombre')->orderBy('nombre')->get();
        $tipos = TipoApartamento::select('id_tipo_apartamento', 'id_proyecto', 'nombre', 'valor_estimado')->orderBy('nombre')->get();
        $estados = EstadoInmueble::select('id_estado_inmueble', 'nombre')->orderBy('nombre')->get();

        // Proyecto del apartamento
        $idProyecto = $a->torre?->id_proyecto;

        // TORRES SOLO DEL PROYECTO ASOCIADO
        $torres = Torre::with('proyecto:id_proyecto,nombre,prima_altura_base,prima_altura_incremento,prima_altura_activa')
            ->where('id_proyecto', $idProyecto)
            ->select('id_torre', 'nombre_torre', 'id_proyecto')
            ->orderBy('nombre_torre')
            ->get();


        // PISOS SOLO DE LA TORRE ASOCIADA
        $pisos = PisoTorre::where('id_torre', $a->id_torre)
            ->select('id_piso_torre', 'nivel', 'id_torre')
            ->orderBy('nivel')
            ->get();

        return Inertia::render('Admin/Apartamento/Edit', [
            'apartamento' => [
                'id_apartamento' => $a->id_apartamento,
                'numero' => $a->numero,
                'id_tipo_apartamento' => $a->id_tipo_apartamento,
                'id_torre' => $a->id_torre,
                'id_piso_torre' => $a->id_piso_torre,
                'id_estado_inmueble' => $a->id_estado_inmueble,
                'valor_total' => $a->valor_total,
                'id_proyecto' => $idProyecto,
            ],
            'proyectos' => $proyectos,
            'tipos' => $tipos,
            'estados' => $estados,

            // datos filtrados correctos
            'torres' => $torres,
            'pisos' => $pisos,
            'empleado' => $empleado,
        ]);
    }


    public function update(Request $request, $id)
    {
        $a = Apartamento::findOrFail($id);

        $validated = $request->validate([
            'numero' => ['required', 'string', 'max:20'],
            'id_tipo_apartamento' => ['required', 'exists:tipos_apartamento,id_tipo_apartamento'],
            'id_torre' => ['required', 'exists:torres,id_torre'],
            'id_piso_torre' => ['required', 'exists:pisos_torre,id_piso_torre'],
            'id_estado_inmueble' => ['required', 'exists:estados_inmueble,id_estado_inmueble'],
            'prima_altura_base' => 'nullable|numeric|min:0',
            'prima_altura_incremento' => 'nullable|numeric|min:0',
            'prima_altura_activa' => 'nullable|boolean',
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
            'prima_altura_base.numeric' => 'La prima altura base debe ser un valor numérico',
            'prima_altura_base.min' => 'La prima altura base no puede ser negativa',
            'prima_altura_incremento.numeric' => 'El incremento debe ser un valor numérico',
            'prima_altura_incremento.min' => 'El incremento no puede ser negativo',
        ]);

        $validated['prima_altura_activa'] = $request->has('prima_altura_activa') ? true : false;

        $torre = Torre::with('proyecto')->findOrFail($validated['id_torre']);
        $proyecto = $torre->proyecto;
        $tipo = TipoApartamento::select('id_tipo_apartamento', 'valor_estimado')
            ->findOrFail($validated['id_tipo_apartamento']);

        $valorBase = (float)($tipo->valor_estimado ?? 0);

        // Coherencia: el piso debe pertenecer a la torre
        $piso = PisoTorre::find($validated['id_piso_torre']);
        if ($piso && $piso->id_torre != $validated['id_torre']) {
            return back()->withErrors(['id_piso_torre' => 'El piso seleccionado no pertenece a la torre indicada'])->withInput();
        }

        $primaAltura = $this->calcularPrimaAltura($validated['id_piso_torre'], $validated['id_torre']);

        $validated['prima_altura'] = $primaAltura;
        $validated['valor_total'] = $valorBase;

        // aplicar política
        $politicaCalc = $this->calcularValorConPolitica($validated['valor_total'], $proyecto->id_proyecto);

        $validated['valor_politica'] = $politicaCalc['valor_politica'];
        $validated['valor_final'] = $politicaCalc['valor_final'] + $primaAltura;


        // Unicidad: número dentro de la misma torre
        $exists = Apartamento::where('numero', $validated['numero'])
            ->where('id_torre', $validated['id_torre'])
            ->where('id_apartamento', '!=', $a->id_apartamento)
            ->exists();

        if ($exists) {
            return back()->withErrors(['numero' => 'Ya existe otro apartamento con este número en la torre seleccionada'])->withInput();
        }

        $a->update($validated);

        return redirect()->route('apartamentos.index')->with('success', 'Apartamento actualizado exitosamente');
    }

    public function destroy($id)
    {
        $a = Apartamento::withCount('parqueaderos')->findOrFail($id);

        if ($a->parqueaderos_count > 0) {
            return back()->withErrors(['delete' => 'No se puede eliminar el apartamento porque tiene parqueaderos asociados']);
        }

        $a->delete();

        return redirect()->route('apartamentos.index')->with('success', 'Apartamento eliminado exitosamente');
    }

    // Auxiliares para selects encadenados
    public function torresPorProyecto($id_proyecto)
    {
        return Torre::with('proyecto:id_proyecto,prima_altura_base,prima_altura_incremento,prima_altura_activa')
            ->where('id_proyecto', $id_proyecto)
            ->select('id_torre', 'nombre_torre', 'id_proyecto', 'nivel_inicio_prima')
            ->orderBy('nombre_torre')
            ->get();
    }

    public function pisosPorTorre($id_torre)
    {
        return PisoTorre::where('id_torre', $id_torre)
            ->select('id_piso_torre', 'nivel', 'id_torre')
            ->orderBy('nivel')
            ->get();
    }

    private function calcularPrimaAltura($idPisoTorre, $idTorre): float
    {
        $piso = PisoTorre::select('id_piso_torre', 'nivel', 'id_torre')->find($idPisoTorre);
        if (!$piso) {
            return 0;
        }
        if ((int)$piso->id_torre !== (int)$idTorre) return 0;

        $nivelActual = (int) $piso->nivel;

        $torre = Torre::with('proyecto:id_proyecto,prima_altura_base,prima_altura_incremento,prima_altura_activa')
            ->select('id_torre', 'id_proyecto', 'nivel_inicio_prima')
            ->find($idTorre);

        if (
            !$torre ||
            !$torre->proyecto ||
            !$torre->proyecto->prima_altura_activa
        ) {
            return 0;
        }

        $nivelBase = (int) ($torre->nivel_inicio_prima ?? 2);

        // Si el piso está por debajo del inicio económico
        if ($nivelActual < $nivelBase) {
            return 0;
        }

        $base = (float) ($torre->proyecto->prima_altura_base ?? 0);
        $incremento = (float) ($torre->proyecto->prima_altura_incremento ?? 0);

        $pisosCalculables = $nivelActual - $nivelBase;



        return $base + ($pisosCalculables * $incremento);
    }


    private function calcularValorConPolitica($valorConPrima, $idProyecto)
    {
        $proyecto = Proyecto::with('politicaVigente')->find($idProyecto);
        if (!$proyecto || !$proyecto->politicaVigente) {
            return [
                'valor_politica' => 0,
                'valor_final' => $valorConPrima
            ];
        }

        $politica = $proyecto->politicaVigente;

        // supuesto: obtener total de apartamentos vendidos del proyecto
        $ventasActuales = \App\Models\Venta::where('id_proyecto', $idProyecto)
            ->whereIn('tipo_operacion', ['venta', 'separacion'])
            ->count();

        $ventasPorEscalon = $politica->ventas_por_escalon ?? 0;
        $porcentajeAumento = $politica->porcentaje_aumento ?? 0;

        // calcular escalón actual
        $escalonActual = $ventasPorEscalon > 0 ? floor($ventasActuales / $ventasPorEscalon) : 0;
        $factor = pow(1 + ($porcentajeAumento / 100), $escalonActual);

        $valorFinal = $valorConPrima * $factor;
        $valorPolitica = $valorFinal - $valorConPrima;

        return [
            'valor_politica' => round($valorPolitica, 2),
            'valor_final' => round($valorFinal, 2)
        ];
    }

    public function scopeDisponiblesPorProyecto($query, int $idProyecto)
    {
        return $query
            ->where('id_estado_inmueble', function ($q) {
                $q->select('id_estado_inmueble')
                    ->from('estados_inmueble')
                    ->where('nombre', 'Disponible')
                    ->limit(1);
            })
            ->whereHas('torre', function ($q) use ($idProyecto) {
                $q->where('id_proyecto', $idProyecto);
            });
    }
}
