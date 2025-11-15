<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\PlanAmortizacionVenta;
use App\Models\Venta;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanAmortizacionVentaWebController extends Controller
{
    public function index()
    {
        $planes = PlanAmortizacionVenta::with('venta')->orderBy('fecha_inicio', 'desc')->get();

        return Inertia::render('Ventas/PlanAmortizacionVenta/Index', [
            'planes' => $planes,
        ]);
    }

    public function create()
    {
        $ventas = Venta::with('cliente')->orderBy('fecha_venta', 'desc')->get();

        return Inertia::render('Ventas/PlanAmortizacionVenta/Create', [
            'ventas' => $ventas,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_venta' => 'required|exists:ventas,id_venta',
            'tipo_plan' => 'nullable|string|max:30',
            'valor_interes_anual' => 'nullable|numeric|min:0',
            'plazo_meses' => 'nullable|integer|min:1',
            'fecha_inicio' => 'nullable|date',
            'observacion' => 'nullable|string|max:300',
        ]);

        PlanAmortizacionVenta::create($validated);

        return redirect()->route('planes-amortizacion-venta.index')->with('success', 'Plan de amortización creado exitosamente.');
    }

    public function show($id)
    {
        $plan = PlanAmortizacionVenta::with(['venta.cliente', 'cuotas'])->findOrFail($id);

        return Inertia::render('Ventas/PlanAmortizacionVenta/Show', [
            'plan' => $plan,
        ]);
    }

    public function edit($id)
    {
        $plan = PlanAmortizacionVenta::findOrFail($id);
        $ventas = Venta::with('cliente')->orderBy('fecha_venta', 'desc')->get();

        return Inertia::render('Ventas/PlanAmortizacionVenta/Edit', [
            'plan' => $plan,
            'ventas' => $ventas,
        ]);
    }

    public function update(Request $request, $id)
    {
        $plan = PlanAmortizacionVenta::findOrFail($id);

        $validated = $request->validate([
            'id_venta' => 'required|exists:ventas,id_venta',
            'tipo_plan' => 'nullable|string|max:30',
            'valor_interes_anual' => 'nullable|numeric|min:0',
            'plazo_meses' => 'nullable|integer|min:1',
            'fecha_inicio' => 'nullable|date',
            'observacion' => 'nullable|string|max:300',
        ]);

        $plan->update($validated);

        return redirect()->route('planes-amortizacion-venta.show', $id)->with('success', 'Plan de amortización actualizado correctamente.');
    }

    public function destroy($id)
    {
        $plan = PlanAmortizacionVenta::findOrFail($id);
        $plan->delete();

        return redirect()->route('planes-amortizacion-venta.index')->with('success', 'Plan de amortización eliminado correctamente.');
    }
}
