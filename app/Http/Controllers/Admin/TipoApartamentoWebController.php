<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipoApartamento;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TipoApartamentoWebController extends Controller
{
    public function index()
    {
        $tipos = TipoApartamento::withCount('apartamentos')
            ->orderBy('nombre')
            ->get()
            ->map(function ($t) {
                return [
                    'id_tipo_apartamento' => $t->id_tipo_apartamento,
                    'nombre' => $t->nombre,
                    'area_construida' => $t->area_construida,
                    'area_privada' => $t->area_privada,
                    'cantidad_habitaciones' => $t->cantidad_habitaciones,
                    'cantidad_banos' => $t->cantidad_banos,
                    'valor_m2' => $t->valor_m2,
                    'apartamentos_count' => $t->apartamentos_count,
                    'valor_estimado' => $t->valor_estimado,
                ];
            });

        return Inertia::render('Admin/TipoApartamento/Index', [
            'tipos' => $tipos,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/TipoApartamento/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'area_construida' => 'nullable|numeric|min:0|max:99999999.99',
            'area_privada' => 'nullable|numeric|min:0|max:99999999.99',
            'cantidad_habitaciones' => 'nullable|integer|min:0|max:32767',
            'cantidad_banos' => 'nullable|integer|min:0|max:32767',
            'valor_m2' => 'nullable|numeric|min:0|max:9999999999999999.99',
        ], [
            'nombre.required' => 'El nombre del tipo de apartamento es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres',
            'area_construida.numeric' => 'El área construida debe ser un valor numérico',
            'area_construida.min' => 'El área construida no puede ser negativa',
            'area_privada.numeric' => 'El área privada debe ser un valor numérico',
            'area_privada.min' => 'El área privada no puede ser negativa',
            'cantidad_habitaciones.integer' => 'La cantidad de habitaciones debe ser un número entero',
            'cantidad_habitaciones.min' => 'La cantidad de habitaciones no puede ser negativa',
            'cantidad_banos.integer' => 'La cantidad de baños debe ser un número entero',
            'cantidad_banos.min' => 'La cantidad de baños no puede ser negativa',
            'valor_m2.numeric' => 'El valor por m² debe ser un valor numérico',
            'valor_m2.min' => 'El valor por m² no puede ser negativo',
        ]);

        // Calcular y persistir valor_estimado
        $area = (float)($validated['area_construida'] ?? 0);
        $valorM2 = (float)($validated['valor_m2'] ?? 0);
        $validated['valor_estimado'] = $area > 0 && $valorM2 > 0 ? $area * $valorM2 : null;

        $tipo = TipoApartamento::create($validated);

        return redirect()->route('tipos-apartamento.show', $tipo->id_tipo_apartamento)
            ->with('success', 'Tipo de apartamento creado exitosamente');
    }

    public function show($id)
    {
        $tipo = TipoApartamento::with(['apartamentos.torre', 'apartamentos.estadoInmueble'])
            ->findOrFail($id);

        return Inertia::render('Admin/TipoApartamento/Show', [
            'tipo' => [
                'id_tipo_apartamento' => $tipo->id_tipo_apartamento,
                'nombre' => $tipo->nombre,
                'area_construida' => $tipo->area_construida,
                'area_privada' => $tipo->area_privada,
                'cantidad_habitaciones' => $tipo->cantidad_habitaciones,
                'cantidad_banos' => $tipo->cantidad_banos,
                'valor_m2' => $tipo->valor_m2,
                'valor_estimado' => $tipo->valor_estimado,
            ],
            'apartamentos' => $tipo->apartamentos->map(function ($a) {
                return [
                    'id_apartamento' => $a->id_apartamento,
                    'numero' => $a->numero,
                    'torre' => $a->torre?->nombre_torre,
                    'estado' => $a->estadoInmueble?->nombre,
                ];
            }),
        ]);
    }

    public function edit($id)
    {
        $t = TipoApartamento::findOrFail($id);

        return Inertia::render('Admin/TipoApartamento/Edit', [
            'tipo' => [
                'id_tipo_apartamento' => $t->id_tipo_apartamento,
                'nombre' => $t->nombre,
                'area_construida' => $t->area_construida,
                'area_privada' => $t->area_privada,
                'cantidad_habitaciones' => $t->cantidad_habitaciones,
                'cantidad_banos' => $t->cantidad_banos,
                'valor_m2' => $t->valor_m2,
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $t = TipoApartamento::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'area_construida' => 'nullable|numeric|min:0|max:99999999.99',
            'area_privada' => 'nullable|numeric|min:0|max:99999999.99',
            'cantidad_habitaciones' => 'nullable|integer|min:0|max:32767',
            'cantidad_banos' => 'nullable|integer|min:0|max:32767',
            'valor_m2' => 'nullable|numeric|min:0|max:9999999999999999.99',
        ], [
            'nombre.required' => 'El nombre del tipo de apartamento es obligatorio',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres',
            'area_construida.numeric' => 'El área construida debe ser un valor numérico',
            'area_construida.min' => 'El área construida no puede ser negativa',
            'area_privada.numeric' => 'El área privada debe ser un valor numérico',
            'area_privada.min' => 'El área privada no puede ser negativa',
            'cantidad_habitaciones.integer' => 'La cantidad de habitaciones debe ser un número entero',
            'cantidad_habitaciones.min' => 'La cantidad de habitaciones no puede ser negativa',
            'cantidad_banos.integer' => 'La cantidad de baños debe ser un número entero',
            'cantidad_banos.min' => 'La cantidad de baños no puede ser negativa',
            'valor_m2.numeric' => 'El valor por m² debe ser un valor numérico',
            'valor_m2.min' => 'El valor por m² no puede ser negativo',
        ]);

        // Recalcular y persistir valor_estimado
        $area = (float)($validated['area_construida'] ?? 0);
        $valorM2 = (float)($validated['valor_m2'] ?? 0);
        $validated['valor_estimado'] = $area > 0 && $valorM2 > 0 ? $area * $valorM2 : null;

        $t->update($validated);

        return redirect()->route('tipos-apartamento.show', $t->id_tipo_apartamento)
            ->with('success', 'Tipo de apartamento actualizado exitosamente');
    }

    public function destroy($id)
    {
        $t = TipoApartamento::withCount('apartamentos')->findOrFail($id);

        if ($t->apartamentos_count > 0) {
            return back()->withErrors(['delete' => 'No se puede eliminar el tipo de apartamento porque tiene apartamentos asociados']);
        }

        $t->delete();

        return redirect()->route('tipos-apartamento.index')->with('success', 'Tipo de apartamento eliminado exitosamente');
    }
}
