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

class ApartamentoWebController extends Controller
{
    public function index()
    {
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
        ]);
    }

    public function create()
    {
        // Selects iniciales
        $proyectos = Proyecto::select('id_proyecto', 'nombre')->orderBy('nombre')->get();
        $tipos = TipoApartamento::select('id_tipo_apartamento', 'nombre', 'valor_estimado')->orderBy('nombre')->get();
        $estados = EstadoInmueble::select('id_estado_inmueble', 'nombre')->orderBy('nombre')->get();
        $torres = Torre::with('proyecto:id_proyecto,nombre,prima_altura_base,prima_altura_incremento,prima_altura_activa')
            ->select('id_torre', 'nombre_torre', 'id_proyecto')
            ->orderBy('nombre_torre')
            ->get();

        $pisos = PisoTorre::select('id_piso_torre', 'nivel', 'id_torre')
            ->orderBy('nivel')
            ->get();

        return Inertia::render('Admin/Apartamento/Create', [
            'proyectos' => $proyectos,
            'tipos' => $tipos,
            'estados' => $estados,
            'torres' => $torres,
            'pisos' => $pisos,
        ]);
    }

    public function store(Request $request)
    {
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

        // Calcular prima altura
        $primaAltura = $this->calcularPrimaAltura($validated['id_piso_torre'], $validated['id_torre']);

        // Calcular valor total
        $validated['prima_altura'] = $primaAltura;
        $validated['valor_total'] = $valorBase;

        // aplicar política
        $politicaCalc = $this->calcularValorConPolitica($validated['valor_total'], $proyecto->id_proyecto);

        $validated['valor_politica'] = $politicaCalc['valor_politica'];
        $validated['valor_final'] = $politicaCalc['valor_final'] + $primaAltura;

        // Coherencia: el piso debe pertenecer a la torre
        $piso = PisoTorre::find($validated['id_piso_torre']);
        if ($piso && $piso->id_torre != $validated['id_torre']) {
            return back()->withErrors(['id_piso_torre' => 'El piso seleccionado no pertenece a la torre indicada'])->withInput();
        }

        // Unicidad: número dentro de la misma torre
        $exists = Apartamento::where('numero', $validated['numero'])
            ->where('id_torre', $validated['id_torre'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['numero' => 'Ya existe un apartamento con este número en la torre seleccionada'])->withInput();
        }

        Apartamento::create($validated);

        return redirect()->route('apartamentos.index')->with('success', 'Apartamento creado exitosamente');
    }

    public function show($id)
    {
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
                ]
            ]
        ]);
    }

    public function edit($id)
    {
        $a = Apartamento::with(['torre.proyecto', 'pisoTorre'])->findOrFail($id);

        $proyectos = Proyecto::select('id_proyecto', 'nombre')->orderBy('nombre')->get();
        $tipos = TipoApartamento::select('id_tipo_apartamento', 'nombre', 'valor_estimado')->orderBy('nombre')->get();
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

        $primaAltura = $this->calcularPrimaAltura($validated['id_piso_torre'], $validated['id_torre']);

        $validated['prima_altura'] = $primaAltura;
        $validated['valor_total'] = $valorBase;

        // aplicar política
        $politicaCalc = $this->calcularValorConPolitica($validated['valor_total'], $proyecto->id_proyecto);

        $validated['valor_politica'] = $politicaCalc['valor_politica'];
        $validated['valor_final'] = $politicaCalc['valor_final'] + $primaAltura;

        // Coherencia: el piso debe pertenecer a la torre
        $piso = PisoTorre::find($validated['id_piso_torre']);
        if ($piso && $piso->id_torre != $validated['id_torre']) {
            return back()->withErrors(['id_piso_torre' => 'El piso seleccionado no pertenece a la torre indicada'])->withInput();
        }

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
        return Torre::where('id_proyecto', $id_proyecto)
            ->select('id_torre', 'nombre_torre', 'id_proyecto')
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
        // Obtener el nivel del piso
        $piso = \App\Models\PisoTorre::select('id_piso_torre', 'nivel')->find($idPisoTorre);
        if (!$piso) {
            return 0;
        }

        $nivel = (int)$piso->nivel;

        // Obtener configuración de prima del proyecto (a través de torre)
        $torre = \App\Models\Torre::with('proyecto:id_proyecto,prima_altura_base,prima_altura_incremento,prima_altura_activa')
            ->select('id_torre', 'id_proyecto')
            ->find($idTorre);

        if (!$torre || !$torre->proyecto || !$torre->proyecto->prima_altura_activa) {
            return 0;
        }

        $proyecto = $torre->proyecto;

        // Prima aplica desde piso 2 en adelante
        if ($nivel < 2) {
            return 0;
        }

        $base = (float)($proyecto->prima_altura_base ?? 0);
        $incremento = (float)($proyecto->prima_altura_incremento ?? 0);

        // Fórmula: base + (nivel - 2) * incremento
        return $base + (($nivel - 2) * $incremento);
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
}
