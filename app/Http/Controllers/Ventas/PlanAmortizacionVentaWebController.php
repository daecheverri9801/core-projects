<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\PlanAmortizacionVenta;
use App\Models\Venta;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanAmortizacionVentaWebController extends Controller
{
    /**
     * Listado de planes
     */
    public function index()
    {
        $planes = PlanAmortizacionVenta::with([
            'venta:id_venta,documento_cliente,valor_total,fecha_venta'
        ])
        ->orderBy('created_at', 'desc')
        ->get();

        return Inertia::render('Ventas/PlanAmortizacionVenta/Index', [
            'planes' => $planes,
        ]);
    }

    /**
     * Crear nuevo plan
     */
    public function create()
    {
        // Solo ventas SIN plan creado
        $ventas = Venta::with(['cliente'])
            ->doesntHave('planAmortizacion')
            ->orderBy('fecha_venta', 'desc')
            ->get(['id_venta', 'documento_cliente', 'valor_total', 'fecha_venta']);

        return Inertia::render('Ventas/PlanAmortizacionVenta/Create', [
            'ventas' => $ventas,
        ]);
    }

    /**
     * Guardar nuevo plan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_venta'            => 'required|exists:ventas,id_venta',
            'tipo_plan'           => 'required|string|max:30',              // obligatorio
            'valor_interes_anual' => 'required|numeric|min:0',              // obligatorio
            'plazo_meses'         => 'required|integer|min:1|max:360',
            'fecha_inicio'        => 'required|date',
            'observacion'         => 'nullable|string|max:300',
        ]);

        // Verificar si la venta ya tiene un plan
        $venta = Venta::findOrFail($validated['id_venta']);

        if ($venta->planAmortizacion) {
            return back()->with('error', 'Esta venta ya tiene un plan de amortización generado.');
        }

        // Crear plan
        $plan = PlanAmortizacionVenta::create($validated);

        return redirect()
            ->route('planes-amortizacion-venta.show', $plan->id_plan)
            ->with('success', 'Plan de amortización creado exitosamente.');
    }

    /**
     * Mostrar plan
     */
    public function show($id)
    {
        $plan = PlanAmortizacionVenta::with([
            'venta.cliente',
            'cuotas' => function ($q) {
                $q->orderBy('numero_cuota', 'asc');
            }
        ])->findOrFail($id);

        return Inertia::render('Ventas/PlanAmortizacionVenta/Show', [
            'plan' => $plan,
        ]);
    }

    /**
     * Editar un plan
     */
    public function edit($id)
    {
        $plan = PlanAmortizacionVenta::findOrFail($id);

        // Las ventas no se deben cambiar si ya tiene cuotas generadas
        $ventas = Venta::orderBy('fecha_venta', 'desc')->get(['id_venta', 'documento_cliente', 'valor_total']);

        return Inertia::render('Ventas/PlanAmortizacionVenta/Edit', [
            'plan' => $plan,
            'ventas' => $ventas,
        ]);
    }

    /**
     * Actualizar plan
     */
    public function update(Request $request, $id)
    {
        $plan = PlanAmortizacionVenta::findOrFail($id);

        $validated = $request->validate([
            'id_venta'            => 'required|exists:ventas,id_venta',
            'tipo_plan'           => 'required|string|max:30',
            'valor_interes_anual' => 'required|numeric|min:0',
            'plazo_meses'         => 'required|integer|min:1|max:360',
            'fecha_inicio'        => 'required|date',
            'observacion'         => 'nullable|string|max:300',
        ]);

        // Si ya tiene cuotas, no permitir cambiar id_venta
        if ($plan->cuotas()->exists() && $validated['id_venta'] != $plan->id_venta) {
            return back()->with('error', 'No puedes cambiar la venta si el plan ya tiene cuotas generadas.');
        }

        $plan->update($validated);

        return redirect()
            ->route('planes-amortizacion-venta.show', $id)
            ->with('success', 'Plan de amortización actualizado correctamente.');
    }

    /**
     * Eliminar plan
     */
    public function destroy($id)
    {
        $plan = PlanAmortizacionVenta::findOrFail($id);

        // eliminar cuotas automáticamente por FK ON DELETE CASCADE
        $plan->delete();

        return redirect()
            ->route('planes-amortizacion-venta.index')
            ->with('success', 'Plan de amortización eliminado correctamente.');
    }
}
