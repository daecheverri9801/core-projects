<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\PlanAmortizacionCuota;
use App\Models\PlanAmortizacionVenta;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanAmortizacionCuotaWebController extends Controller
{
    public function index()
    {
        $cuotas = PlanAmortizacionCuota::with('planAmortizacion.venta')
            ->orderBy('fecha_vencimiento')
            ->get();

        return Inertia::render('Ventas/PlanAmortizacionCuota/Index', [
            'cuotas' => $cuotas,
        ]);
    }

    public function create()
    {
        $planes = PlanAmortizacionVenta::with('venta.cliente')->get();

        return Inertia::render('Ventas/PlanAmortizacionCuota/Create', [
            'planes' => $planes,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_plan' => 'required|exists:planes_amortizacion_venta,id_plan',
            'numero_cuota' => 'nullable|integer|min:1',
            'fecha_vencimiento' => 'nullable|date',
            'valor_cuota' => 'nullable|numeric|min:0',
            'valor_interes' => 'nullable|numeric|min:0',
            'valor_capital' => 'nullable|numeric|min:0',
            'saldo' => 'nullable|numeric|min:0',
            'estado' => 'nullable|string|max:20',
        ]);

        PlanAmortizacionCuota::create($validated);

        return redirect()->route('planes-amortizacion-cuota.index')->with('success', 'Cuota creada exitosamente.');
    }

    public function show($id)
    {
        $cuota = PlanAmortizacionCuota::with(['planAmortizacion.venta.cliente', 'pagos'])->findOrFail($id);

        return Inertia::render('Ventas/PlanAmortizacionCuota/Show', [
            'cuota' => $cuota,
        ]);
    }

    public function edit($id)
    {
        $cuota = PlanAmortizacionCuota::findOrFail($id);
        $planes = PlanAmortizacionVenta::with('venta.cliente')->get();

        return Inertia::render('Ventas/PlanAmortizacionCuota/Edit', [
            'cuota' => $cuota,
            'planes' => $planes,
        ]);
    }

    public function update(Request $request, $id)
    {
        $cuota = PlanAmortizacionCuota::findOrFail($id);

        $validated = $request->validate([
            'id_plan' => 'required|exists:planes_amortizacion_venta,id_plan',
            'numero_cuota' => 'nullable|integer|min:1',
            'fecha_vencimiento' => 'nullable|date',
            'valor_cuota' => 'nullable|numeric|min:0',
            'valor_interes' => 'nullable|numeric|min:0',
            'valor_capital' => 'nullable|numeric|min:0',
            'saldo' => 'nullable|numeric|min:0',
            'estado' => 'nullable|string|max:20',
        ]);

        $cuota->update($validated);

        return redirect()->route('planes-amortizacion-cuota.show', $id)->with('success', 'Cuota actualizada correctamente.');
    }

    public function destroy($id)
    {
        $cuota = PlanAmortizacionCuota::findOrFail($id);
        $cuota->delete();

        return redirect()->route('planes-amortizacion-cuota.index')->with('success', 'Cuota eliminada correctamente.');
    }
}
