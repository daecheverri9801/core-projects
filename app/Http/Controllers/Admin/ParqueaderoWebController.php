<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Parqueadero;
use App\Models\Apartamento;
use App\Models\Proyecto;
use App\Models\Torre;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Support\RedirectBackTo;

class ParqueaderoWebController extends Controller
{
    public function index(Request $request)
    {
        $empleado = $request->user()->load('cargo');

        $parqueaderos = Parqueadero::with([
            'proyecto:id_proyecto,nombre',
            'torre:id_torre,nombre_torre',
            'apartamento:id_apartamento,numero',
        ])
            ->orderBy('id_parqueadero', 'desc')
            ->get()
            ->map(function ($p) {
                return [
                    'id_parqueadero' => $p->id_parqueadero,
                    'numero' => $p->numero,
                    'tipo' => $p->tipo,
                    'precio' => $p->precio,
                    'estado' => $p->id_apartamento ? 'Asignado' : 'Disponible',
                    'apartamento' => $p->apartamento?->numero,
                    'torre' => $p->torre?->nombre_torre,
                    'id_proyecto' => $p->proyecto?->id_proyecto,
                    'proyecto' => $p->proyecto?->nombre,
                ];
            });

        return Inertia::render('Admin/Parqueadero/Index', [
            'parqueaderos' => $parqueaderos,
            'empleado' => $empleado,
        ]);
    }

    public function create(Request $request)
    {
        $empleado = $request->user()->load('cargo');

        $proyectos = Proyecto::select('id_proyecto', 'nombre')
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Admin/Parqueadero/Create', [
            'proyectos' => $proyectos,
            'torres' => [],
            'apartamentos' => [], // se cargan por torre
            'tipos' => ['Vehiculo', 'Moto'],
            'empleado' => $empleado,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_torre' => 'required|exists:torres,id_torre',
            'parqueaderos' => 'required|array|min:1',
            'parqueaderos.*.numero' => 'required|string|max:20',
            'parqueaderos.*.tipo' => 'required|string|in:Vehiculo,Moto|max:20',
            'parqueaderos.*.precio' => 'nullable|numeric|min:0',
            'parqueaderos.*.id_apartamento' => 'nullable|exists:apartamentos,id_apartamento',
        ], [
            'id_torre.required' => 'La torre es obligatoria',
            'id_torre.exists' => 'La torre seleccionada no existe',
            'parqueaderos.required' => 'Debes agregar al menos un parqueadero',
            'parqueaderos.*.numero.required' => 'El número es obligatorio',
            'parqueaderos.*.tipo.required' => 'El tipo es obligatorio',
            'parqueaderos.*.tipo.in' => 'El tipo debe ser Vehiculo o Moto',
            'parqueaderos.*.precio.numeric' => 'El precio debe ser numérico',
            'parqueaderos.*.id_apartamento.exists' => 'El apartamento seleccionado no existe',
        ]);

        $idTorre = (int) $validated['id_torre'];

        $torre = \App\Models\Torre::select('id_torre', 'id_proyecto')
            ->findOrFail($idTorre);

        $idProyecto = (int) $torre->id_proyecto;

        // duplicados en request por numero
        $nums = collect($validated['parqueaderos'])
            ->pluck('numero')
            ->map(fn($n) => trim((string)$n));

        $dupNums = $nums->duplicates()->values();
        if ($dupNums->isNotEmpty()) {
            return back()->withErrors([
                'general' => 'Hay números repetidos en el listado: ' . $dupNums->unique()->implode(', ')
            ]);
        }

        // existentes en DB (misma torre)
        $existentes = \App\Models\Parqueadero::where('id_torre', $idTorre)
            ->whereIn('numero', $nums->all())
            ->pluck('numero');

        if ($existentes->isNotEmpty()) {
            return back()->withErrors([
                'general' => 'Ya existen en la torre estos números: ' . $existentes->implode(', ')
            ]);
        }

        // validar que los apartamentos asignados pertenezcan a la misma torre
        $aptosAsignados = collect($validated['parqueaderos'])
            ->pluck('id_apartamento')
            ->filter()
            ->map(fn($x) => (int) $x)
            ->values();

        if ($aptosAsignados->isNotEmpty()) {
            $aptosValidos = \App\Models\Apartamento::whereIn('id_apartamento', $aptosAsignados->all())
                ->where('id_torre', $idTorre)
                ->pluck('id_apartamento')
                ->map(fn($x) => (int) $x);

            $invalidos = $aptosAsignados->diff($aptosValidos);

            if ($invalidos->isNotEmpty()) {
                return back()->withErrors([
                    'general' => 'Hay apartamentos que no pertenecen a la torre seleccionada: ' . $invalidos->implode(', ')
                ]);
            }

            // (opcional) evitar duplicados de apartamento en el mismo request
            $dupAptos = $aptosAsignados->duplicates()->values();
            if ($dupAptos->isNotEmpty()) {
                return back()->withErrors([
                    'general' => 'Hay apartamentos repetidos en el listado: ' . $dupAptos->unique()->implode(', ')
                ]);
            }

            // (opcional) evitar asignar a apto que ya tiene parqueadero
            // si NO usaste el índice parcial unique, valida aquí:
            $ocupados = \App\Models\Parqueadero::whereIn('id_apartamento', $aptosAsignados->all())
                ->whereNotNull('id_apartamento')
                ->pluck('id_apartamento');

            if ($ocupados->isNotEmpty()) {
                return back()->withErrors([
                    'general' => 'Estos apartamentos ya tienen parqueadero asignado: ' . $ocupados->implode(', ')
                ]);
            }
        }

        $rows = collect($validated['parqueaderos'])->map(function ($p) use ($idProyecto, $idTorre) {
            return [
                'numero' => trim((string)$p['numero']),
                'tipo' => $p['tipo'],
                'precio' => $p['precio'] ?? null,
                'id_proyecto' => $idProyecto,
                'id_torre' => $idTorre,
                'id_apartamento' => $p['id_apartamento'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->all();

        \App\Models\Parqueadero::insert($rows);

        return redirect()->route('zonas-sociales.create')
            ->with('success', 'Parqueaderos creados exitosamente');
    }

    public function show(Request $request, $id)
    {
        $empleado = $request->user()->load('cargo');

        $p = Parqueadero::with([
            'proyecto.ubicacion.ciudad',
            'torre.proyecto.ubicacion.ciudad',
            'apartamento.torre.proyecto.ubicacion.ciudad',
            'apartamento.pisoTorre',
            'apartamento.tipoApartamento',
            'apartamento.estadoInmueble'
        ])->findOrFail($id);

        $proyecto = $p->proyecto ?? $p->torre?->proyecto ?? $p->apartamento?->torre?->proyecto;

        $resumen = [
            'id_parqueadero' => $p->id_parqueadero,
            'numero' => $p->numero,
            'tipo' => $p->tipo,
            'precio' => $p->precio,
            'estado' => $p->id_apartamento ? 'Asignado' : 'Disponible',
            'proyecto' => $proyecto?->nombre,
            'torre' => $p->torre?->nombre_torre ?? $p->apartamento?->torre?->nombre_torre,
        ];

        if ($p->apartamento) {
            $resumen['apartamento'] = [
                'numero' => $p->apartamento->numero,
                'tipo' => $p->apartamento->tipoApartamento->nombre ?? null,
                'torre' => $p->apartamento->torre->nombre_torre ?? null,
                'piso' => $p->apartamento->pisoTorre->nivel ?? null,
                'proyecto' => $p->apartamento->torre->proyecto->nombre ?? null,
                'ubicacion' => optional($p->apartamento->torre->proyecto->ubicacion, function ($u) {
                    $ciudad = $u->ciudad->nombre ?? '';
                    return trim(($u->direccion ?? '') . (strlen($ciudad) ? ', ' . $ciudad : ''));
                }),
                'estado_inmueble' => $p->apartamento->estadoInmueble->nombre ?? null,
            ];
        }

        return Inertia::render('Admin/Parqueadero/Show', [
            'parqueadero' => $p,
            'resumen' => $resumen,
            'empleado' => $empleado,
        ]);
    }

    public function edit(Request $request, $id)
    {
        $empleado = $request->user()->load('cargo');

        $p = Parqueadero::with(['proyecto', 'torre', 'apartamento.torre.proyecto'])->findOrFail($id);

        $proyectos = Proyecto::select('id_proyecto', 'nombre')->orderBy('nombre')->get();
        $torres = Torre::select('id_torre', 'id_proyecto', 'nombre_torre')->orderBy('nombre_torre')->get();
        $apartamentos = Apartamento::select('id_apartamento', 'numero', 'id_torre')->orderBy('numero')->get();

        return Inertia::render('Admin/Parqueadero/Edit', [
            'parqueadero' => [
                'id_parqueadero' => $p->id_parqueadero,
                'numero' => $p->numero,
                'tipo' => $p->tipo,
                'precio' => $p->precio,
                'id_apartamento' => $p->id_apartamento,
                'id_proyecto' => $p->id_proyecto ?? $p->torre?->id_proyecto,
                'id_torre' => $p->id_torre,
            ],
            'proyectos' => $proyectos,
            'torres' => $torres,
            'apartamentos' => $apartamentos,
            'tipos' => ['Vehiculo', 'Moto'],
            'empleado' => $empleado,
        ]);
    }

    public function update(Request $request, $id)
    {
        $p = Parqueadero::findOrFail($id);

        $validated = $request->validate([
            'numero' => 'required|string|max:20',
            'tipo' => 'required|string|in:Vehiculo,Moto|max:20',
            'precio' => 'nullable|numeric|min:0',
            'id_apartamento' => 'nullable|exists:apartamentos,id_apartamento',
        ]);

        // unique por torre (id_torre del parqueadero)
        $exists = Parqueadero::where('id_torre', $p->id_torre)
            ->where('numero', $validated['numero'])
            ->where('id_parqueadero', '!=', $p->id_parqueadero)
            ->exists();

        if ($exists) {
            return back()->withErrors(['numero' => 'Ya existe un parqueadero con este número en la torre.']);
        }

        $p->update([
            'numero' => $validated['numero'],
            'tipo' => $validated['tipo'],
            'precio' => $validated['precio'] ?? null,
            'id_apartamento' => $validated['id_apartamento'] ?? null,
        ]);

        return redirect()->route('parqueaderos.show', $p->id_parqueadero)
            ->with('success', 'Parqueadero actualizado exitosamente');
    }

    public function destroy($id)
    {
        $p = Parqueadero::findOrFail($id);
        $p->delete();

        return redirect()->route('parqueaderos.index')->with('success', 'Parqueadero eliminado exitosamente');
    }

    /** Auxiliar: torres por proyecto (para el select encadenado) */
    public function torresPorProyecto($id_proyecto)
    {
        return Torre::where('id_proyecto', $id_proyecto)
            ->select('id_torre', 'id_proyecto', 'nombre_torre')
            ->orderBy('nombre_torre')
            ->get();
    }

    /** Auxiliar: apartamentos por torre (para asignación opcional) */
    public function apartamentosPorTorre($id_torre)
    {
        return Apartamento::with(['torre:id_torre,nombre_torre'])
            ->where('id_torre', $id_torre)
            ->select('id_apartamento', 'numero', 'id_torre')
            ->orderByRaw('LPAD(numero, 10, \'0\') ASC') // opcional para ordenar 1,2,10...
            ->get()
            ->map(fn($a) => [
                'id_apartamento' => $a->id_apartamento,
                'numero' => $a->numero,
                'torre' => $a->torre?->nombre_torre,
            ]);
    }
}
