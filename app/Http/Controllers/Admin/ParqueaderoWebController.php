<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Parqueadero;
use App\Models\Apartamento;
use App\Models\Proyecto;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Support\RedirectBackTo;

class ParqueaderoWebController extends Controller
{
    public function index(Request $request)
    {
        $empleado = $request->user()->load('cargo');
        $parqueaderos = Parqueadero::with([
            'apartamento.torre.proyecto',
            'apartamento.pisoTorre'
        ])
            ->orderBy('id_parqueadero', 'desc')
            ->get()
            ->map(function ($p) {
                $proyecto = $p->apartamento?->torre?->proyecto;

                return [
                    'id_parqueadero' => $p->id_parqueadero,
                    'numero' => $p->numero,
                    'tipo' => $p->tipo,
                    'estado' => $p->id_apartamento ? 'Asignado' : 'Disponible',
                    'apartamento' => $p->apartamento?->numero,
                    'torre' => $p->apartamento?->torre?->nombre_torre,
                    'piso' => $p->apartamento?->pisoTorre?->nivel,
                    'id_proyecto' => $proyecto?->id_proyecto,      // ✅ clave para agrupar
                    'proyecto' => $proyecto?->nombre,              // ✅ nombre para mostrar
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
        // Para el select de Apartamento (opcional), cargamos todos o puedes filtrar por proyecto si deseas
        $apartamentos = Apartamento::with('torre.proyecto')
            ->select('id_apartamento', 'numero', 'id_torre', 'id_piso_torre')
            ->orderBy('id_apartamento', 'desc')
            ->get()
            ->map(function ($a) {
                return [
                    'id_apartamento' => $a->id_apartamento,
                    'numero' => $a->numero,
                    'torre' => $a->torre?->nombre_torre,
                    'proyecto' => $a->torre?->proyecto?->nombre,
                ];
            });

        return Inertia::render('Admin/Parqueadero/Create', [
            'apartamentos' => $apartamentos,
            'tipos' => ['Vehiculo', 'Moto'],
            'empleado' => $empleado,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero' => 'required|string|max:20|unique:parqueaderos,numero',
            'tipo' => 'required|string|in:Vehiculo,Moto|max:20',
            'id_apartamento' => 'nullable|exists:apartamentos,id_apartamento',
        ], [
            'numero.required' => 'El número del parqueadero es obligatorio',
            'numero.max' => 'El número del parqueadero no puede exceder 20 caracteres',
            'numero.unique' => 'Ya existe un parqueadero con este número',
            'tipo.required' => 'El tipo de parqueadero es obligatorio',
            'tipo.max' => 'El tipo no puede exceder 20 caracteres',
            'tipo.in' => 'El tipo debe ser Vehiculo o Moto',
            'id_apartamento.exists' => 'El apartamento seleccionado no existe',
        ]);

        // Regla adicional: si id_apartamento viene, validar que ese parqueadero no esté asignado ya (no aplica porque es nuevo)
        $parqueadero = Parqueadero::create($validated);

        return RedirectBackTo::respond(
            $request,
            'parqueaderos.index',
            [],
            'Parqueadero creado exitosamente',
            ['id_parqueadero' => $parqueadero->id_parqueadero]
        );
    }

    public function show(Request $request, $id)
    {
        $empleado = $request->user()->load('cargo');
        $p = Parqueadero::with([
            'apartamento.torre.proyecto.ubicacion.ciudad',
            'apartamento.pisoTorre',
            'apartamento.tipoApartamento',
            'apartamento.estadoInmueble'
        ])->findOrFail($id);

        // Resumen estilo API
        $resumen = [
            'id_parqueadero' => $p->id_parqueadero,
            'numero' => $p->numero,
            'tipo' => $p->tipo,
            'estado' => $p->id_apartamento ? 'Asignado' : 'Disponible',
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
        $p = Parqueadero::with('apartamento.torre.proyecto')->findOrFail($id);

        $apartamentos = Apartamento::with('torre.proyecto')
            ->select('id_apartamento', 'numero', 'id_torre', 'id_piso_torre')
            ->orderBy('id_apartamento', 'desc')
            ->get()
            ->map(function ($a) {
                return [
                    'id_apartamento' => $a->id_apartamento,
                    'numero' => $a->numero,
                    'torre' => $a->torre?->nombre_torre,
                    'proyecto' => $a->torre?->proyecto?->nombre,
                ];
            });

        return Inertia::render('Admin/Parqueadero/Edit', [
            'parqueadero' => [
                'id_parqueadero' => $p->id_parqueadero,
                'numero' => $p->numero,
                'tipo' => $p->tipo,
                'id_apartamento' => $p->id_apartamento,
            ],
            'apartamentos' => $apartamentos,
            'tipos' => ['Vehiculo', 'Moto'],
            'empleado' => $empleado,
        ]);
    }

    public function update(Request $request, $id)
    {
        $p = Parqueadero::findOrFail($id);

        $validated = $request->validate([
            'numero' => 'required|string|max:20|unique:parqueaderos,numero,' . $id . ',id_parqueadero',
            'tipo' => 'required|string|in:Vehiculo,Moto|max:20',
            'id_apartamento' => 'nullable|exists:apartamentos,id_apartamento',
        ], [
            'numero.required' => 'El número del parqueadero es obligatorio',
            'numero.max' => 'El número del parqueadero no puede exceder 20 caracteres',
            'numero.unique' => 'Ya existe otro parqueadero con este número',
            'tipo.required' => 'El tipo de parqueadero es obligatorio',
            'tipo.max' => 'El tipo no puede exceder 20 caracteres',
            'tipo.in' => 'El tipo debe ser Vehiculo o Moto',
            'id_apartamento.exists' => 'El apartamento seleccionado no existe',
        ]);

        $p->update($validated);

        return redirect()->route('parqueaderos.show', $p->id_parqueadero)->with('success', 'Parqueadero actualizado exitosamente');
    }

    public function destroy($id)
    {
        $p = Parqueadero::findOrFail($id);
        $p->delete();

        return redirect()->route('parqueaderos.index')->with('success', 'Parqueadero eliminado exitosamente');
    }
}
