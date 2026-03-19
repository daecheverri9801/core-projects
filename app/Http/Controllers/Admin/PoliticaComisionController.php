<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Models\PoliticaComision;
use App\Models\Proyecto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PoliticaComisionController extends Controller
{
    public function index(Request $request)
    {
        $empleado = $request->user()->load('cargo');

        $politicas = PoliticaComision::query()
            ->with([
                'proyecto:id_proyecto,nombre,fecha_inicio,fecha_finalizacion',
                'empleado:id_empleado,nombre,apellido,id_cargo',
                'empleado.cargo:id_cargo,nombre',
            ])
            ->orderByDesc('id_politica_comision')
            ->get()
            ->map(function ($politica) {
                return [
                    'id_politica_comision' => $politica->id_politica_comision,
                    'id_proyecto' => $politica->id_proyecto,
                    'id_empleado' => $politica->id_empleado,
                    'tipo_comision' => $politica->tipo_comision,
                    'porcentaje' => $politica->porcentaje !== null ? (float) $politica->porcentaje : null,
                    'vigente_desde' => optional($politica->vigente_desde)->format('Y-m-d'),
                    'vigente_hasta' => optional($politica->vigente_hasta)->format('Y-m-d'),
                    'proyecto' => $politica->proyecto ? [
                        'id_proyecto' => $politica->proyecto->id_proyecto,
                        'nombre' => $politica->proyecto->nombre,
                        'fecha_inicio' => $politica->proyecto->fecha_inicio,
                        'fecha_finalizacion' => $politica->proyecto->fecha_finalizacion,
                    ] : null,
                    'empleado' => $politica->empleado ? [
                        'id_empleado' => $politica->empleado->id_empleado,
                        'nombre' => trim(($politica->empleado->nombre ?? '') . ' ' . ($politica->empleado->apellido ?? '')),
                        'cargo' => $politica->empleado->cargo?->nombre,
                    ] : null,
                ];
            })
            ->values();

        return Inertia::render('Admin/PoliticasComision/Index', [
            'politicas' => $politicas,
            'empleado' => $empleado,
        ]);
    }

    public function create(Request $request)
    {
        $empleado = $request->user()->load('cargo');
        $proyectoSeleccionado = $request->query('proyecto');

        $proyectos = Proyecto::query()
            ->orderBy('nombre')
            ->get(['id_proyecto', 'nombre', 'fecha_inicio', 'fecha_finalizacion']);

        $empleadosComisionables = Empleado::query()
            ->with('cargo:id_cargo,nombre')
            ->whereHas('cargo', function ($q) {
                $q->whereIn('nombre', ['Asesora Comercial', 'Directora Comercial']);
            })
            ->orderBy('nombre')
            ->get(['id_empleado', 'nombre', 'apellido', 'id_cargo'])
            ->map(function ($emp) {
                return [
                    'id_empleado' => $emp->id_empleado,
                    'nombre' => trim(($emp->nombre ?? '') . ' ' . ($emp->apellido ?? '')),
                    'id_cargo' => $emp->id_cargo,
                    'cargo' => $emp->cargo?->nombre,
                ];
            })
            ->values();

        return Inertia::render('Admin/PoliticasComision/Create', [
            'empleado' => $empleado,
            'proyectos' => $proyectos,
            'empleadosComisionables' => $empleadosComisionables,
            'proyectoSeleccionado' => $proyectoSeleccionado ? (string) $proyectoSeleccionado : null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_proyecto' => ['required', 'integer', 'exists:proyectos,id_proyecto'],
            'id_empleado' => ['required', 'integer', 'exists:empleados,id_empleado'],
            'tipo_comision' => ['required', 'in:venta_propia,venta_equipo'],
            'porcentaje' => ['required', 'numeric', 'min:0', 'max:999.999'],
        ]);

        $proyecto = Proyecto::query()->findOrFail($validated['id_proyecto']);
        $empleado = Empleado::query()->with('cargo:id_cargo,nombre')->findOrFail($validated['id_empleado']);

        $cargoNombre = $empleado->cargo?->nombre;

        if (!in_array($cargoNombre, ['Asesora Comercial', 'Directora Comercial'], true)) {
            return back()
                ->withErrors([
                    'id_empleado' => 'El empleado seleccionado no pertenece a un cargo válido para comisión.',
                ])
                ->withInput();
        }

        if ($cargoNombre === 'Asesora Comercial' && $validated['tipo_comision'] !== 'venta_propia') {
            return back()
                ->withErrors([
                    'tipo_comision' => 'Para Asesora Comercial solo se permite Venta propia.',
                ])
                ->withInput();
        }

        $exists = PoliticaComision::query()
            ->where('id_proyecto', $validated['id_proyecto'])
            ->where('id_empleado', $validated['id_empleado'])
            ->where('tipo_comision', $validated['tipo_comision'])
            ->exists();

        if ($exists) {
            return back()
                ->withErrors([
                    'id_empleado' => 'Ya existe una política para este proyecto, empleado y tipo de comisión.',
                ])
                ->withInput();
        }

        $politica = PoliticaComision::create([
            'id_proyecto' => $validated['id_proyecto'],
            'id_empleado' => $validated['id_empleado'],
            'tipo_comision' => $validated['tipo_comision'],
            'porcentaje' => $validated['porcentaje'],
            'vigente_desde' => $proyecto->fecha_inicio,
            'vigente_hasta' => $proyecto->fecha_finalizacion,
        ]);

        if ($request->boolean('from_politicas_precio_create')) {
            return redirect()
                ->to('/politicas-precio-proyecto/crear?proyecto=' . $validated['id_proyecto'])
                ->with('success', 'Política de comisión registrada correctamente.');
        }

        return redirect()
            ->route('politicas-comision.show', $politica->id_politica_comision)
            ->with('success', 'Política de comisión registrada correctamente.');
    }

    public function show(Request $request, PoliticaComision $politicaComision)
    {
        $empleado = $request->user()->load('cargo');

        $politicaComision->load([
            'proyecto:id_proyecto,nombre,fecha_inicio,fecha_finalizacion',
            'empleado:id_empleado,nombre,apellido,id_cargo',
            'empleado.cargo:id_cargo,nombre',
        ]);

        return Inertia::render('Admin/PoliticasComision/Show', [
            'empleado' => $empleado,
            'politica' => [
                'id_politica_comision' => $politicaComision->id_politica_comision,
                'id_proyecto' => $politicaComision->id_proyecto,
                'id_empleado' => $politicaComision->id_empleado,
                'tipo_comision' => $politicaComision->tipo_comision,
                'porcentaje' => $politicaComision->porcentaje !== null ? (float) $politicaComision->porcentaje : null,
                'vigente_desde' => optional($politicaComision->vigente_desde)->format('Y-m-d'),
                'vigente_hasta' => optional($politicaComision->vigente_hasta)->format('Y-m-d'),
                'proyecto' => $politicaComision->proyecto ? [
                    'id_proyecto' => $politicaComision->proyecto->id_proyecto,
                    'nombre' => $politicaComision->proyecto->nombre,
                    'fecha_inicio' => $politicaComision->proyecto->fecha_inicio,
                    'fecha_finalizacion' => $politicaComision->proyecto->fecha_finalizacion,
                ] : null,
                'empleado' => $politicaComision->empleado ? [
                    'id_empleado' => $politicaComision->empleado->id_empleado,
                    'nombre' => trim(($politicaComision->empleado->nombre ?? '') . ' ' . ($politicaComision->empleado->apellido ?? '')),
                    'cargo' => $politicaComision->empleado->cargo?->nombre,
                ] : null,
            ],
        ]);
    }

    public function edit(Request $request, PoliticaComision $politicaComision)
    {
        $empleado = $request->user()->load('cargo');

        $politicaComision->load([
            'proyecto:id_proyecto,nombre,fecha_inicio,fecha_finalizacion',
            'empleado:id_empleado,nombre,apellido,id_cargo',
            'empleado.cargo:id_cargo,nombre',
        ]);

        $proyectos = Proyecto::query()
            ->orderBy('nombre')
            ->get(['id_proyecto', 'nombre', 'fecha_inicio', 'fecha_finalizacion']);

        $empleadosComisionables = Empleado::query()
            ->with('cargo:id_cargo,nombre')
            ->whereHas('cargo', function ($q) {
                $q->whereIn('nombre', ['Asesora Comercial', 'Directora Comercial']);
            })
            ->orderBy('nombre')
            ->get(['id_empleado', 'nombre', 'apellido', 'id_cargo'])
            ->map(function ($emp) {
                return [
                    'id_empleado' => $emp->id_empleado,
                    'nombre' => trim(($emp->nombre ?? '') . ' ' . ($emp->apellido ?? '')),
                    'id_cargo' => $emp->id_cargo,
                    'cargo' => $emp->cargo?->nombre,
                ];
            })
            ->values();

        return Inertia::render('Admin/PoliticasComision/Edit', [
            'empleado' => $empleado,
            'proyectos' => $proyectos,
            'empleadosComisionables' => $empleadosComisionables,
            'politica' => [
                'id_politica_comision' => $politicaComision->id_politica_comision,
                'id_proyecto' => $politicaComision->id_proyecto,
                'id_empleado' => $politicaComision->id_empleado,
                'tipo_comision' => $politicaComision->tipo_comision,
                'porcentaje' => $politicaComision->porcentaje !== null ? (float) $politicaComision->porcentaje : null,
                'vigente_desde' => optional($politicaComision->vigente_desde)->format('Y-m-d'),
                'vigente_hasta' => optional($politicaComision->vigente_hasta)->format('Y-m-d'),
                'proyecto' => $politicaComision->proyecto ? [
                    'id_proyecto' => $politicaComision->proyecto->id_proyecto,
                    'nombre' => $politicaComision->proyecto->nombre,
                    'fecha_inicio' => $politicaComision->proyecto->fecha_inicio,
                    'fecha_finalizacion' => $politicaComision->proyecto->fecha_finalizacion,
                ] : null,
                'empleado' => $politicaComision->empleado ? [
                    'id_empleado' => $politicaComision->empleado->id_empleado,
                    'nombre' => trim(($politicaComision->empleado->nombre ?? '') . ' ' . ($politicaComision->empleado->apellido ?? '')),
                    'cargo' => $politicaComision->empleado->cargo?->nombre,
                ] : null,
            ],
        ]);
    }

    public function update(Request $request, PoliticaComision $politicaComision)
    {
        $validated = $request->validate([
            'id_proyecto' => ['required', 'integer', 'exists:proyectos,id_proyecto'],
            'id_empleado' => ['required', 'integer', 'exists:empleados,id_empleado'],
            'tipo_comision' => ['required', 'in:venta_propia,venta_equipo'],
            'porcentaje' => ['required', 'numeric', 'min:0', 'max:999.999'],
        ]);

        $proyecto = Proyecto::query()->findOrFail($validated['id_proyecto']);
        $empleado = Empleado::query()->with('cargo:id_cargo,nombre')->findOrFail($validated['id_empleado']);

        $cargoNombre = $empleado->cargo?->nombre;

        if (!in_array($cargoNombre, ['Asesora Comercial', 'Directora Comercial'], true)) {
            return back()
                ->withErrors([
                    'id_empleado' => 'El empleado seleccionado no pertenece a un cargo válido para comisión.',
                ])
                ->withInput();
        }

        if ($cargoNombre === 'Asesora Comercial' && $validated['tipo_comision'] !== 'venta_propia') {
            return back()
                ->withErrors([
                    'tipo_comision' => 'Para Asesora Comercial solo se permite Venta propia.',
                ])
                ->withInput();
        }

        $exists = PoliticaComision::query()
            ->where('id_proyecto', $validated['id_proyecto'])
            ->where('id_empleado', $validated['id_empleado'])
            ->where('tipo_comision', $validated['tipo_comision'])
            ->where('id_politica_comision', '!=', $politicaComision->id_politica_comision)
            ->exists();

        if ($exists) {
            return back()
                ->withErrors([
                    'id_empleado' => 'Ya existe una política para este proyecto, empleado y tipo de comisión.',
                ])
                ->withInput();
        }

        $politicaComision->update([
            'id_proyecto' => $validated['id_proyecto'],
            'id_empleado' => $validated['id_empleado'],
            'tipo_comision' => $validated['tipo_comision'],
            'porcentaje' => $validated['porcentaje'],
            'vigente_desde' => $proyecto->fecha_inicio,
            'vigente_hasta' => $proyecto->fecha_finalizacion,
        ]);

        return redirect()
            ->route('politicas-comision.show', $politicaComision->id_politica_comision)
            ->with('success', 'Política de comisión actualizada correctamente.');
    }

    public function destroy(PoliticaComision $politicaComision)
    {
        $politicaComision->delete();

        return redirect()
            ->route('politicas-comision.index')
            ->with('success', 'Política de comisión eliminada correctamente.');
    }
}
