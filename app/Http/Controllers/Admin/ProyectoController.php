<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proyecto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use App\Models\Estado;
use App\Models\Ubicacion;
use App\Models\Torre;

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

    public function create(Request $request)
    {
        $empleado = $request->user()->load('cargo');
        $estados = Estado::all();
        $ubicaciones = Ubicacion::with('ciudad')->get();

        return Inertia::render('Admin/Proyectos/Create', compact('estados', 'ubicaciones', 'empleado'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:150',
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
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $proyecto = Proyecto::create($request->all());
        $proyecto->load(['estado_proyecto', 'ubicacion.ciudad.departamento.pais']);

        return redirect()->route('proyectos.index')->with('success', 'Proyecto creado exitosamente');
    }

    public function show(Request $request, $id_proyecto)
    {
        $empleado = $request->user()->load('cargo');
        $tab = request()->get('tab', 'detalle');
        $search = request()->get('search');

        $proyecto = Proyecto::with(['ubicacion.ciudad', 'estado_proyecto', 'politicasPrecio' => function ($query) {
            $query->orderBy('aplica_desde', 'desc');
        }])->findOrFail($id_proyecto);

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

    public function edit(Proyecto $proyecto, Request $request)
    {
        $empleado = $request->user()->load('cargo');
        $estados = Estado::all();
        $ubicaciones = Ubicacion::with('ciudad')->get();

        return Inertia::render('Admin/Proyectos/Edit', compact('proyecto', 'estados', 'ubicaciones', 'empleado'));
    }

    public function update(Request $request, Proyecto $proyecto)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:150',
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
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $proyecto->update($request->all());
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
}
