<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Venta;
use App\Models\ConceptoPago;
use App\Models\MedioPago;
use App\Models\PlanAmortizacionCuota;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PagoWebController extends Controller
{
    public function index()
    {
        $pagos = Pago::with(['venta.cliente', 'conceptoPago', 'medioPago', 'cuota'])
            ->orderBy('fecha', 'desc')
            ->get();

        return Inertia::render('Ventas/Pago/Index', [
            'pagos' => $pagos,
        ]);
    }

    public function create()
    {
        $ventas = Venta::with('cliente')->orderBy('fecha_venta', 'desc')->get();
        $conceptos = ConceptoPago::orderBy('concepto')->get();
        $medios = MedioPago::orderBy('medio_pago')->get();
        $cuotas = PlanAmortizacionCuota::with('planAmortizacion.venta')->get();

        return Inertia::render('Ventas/Pago/Create', [
            'ventas' => $ventas,
            'conceptos' => $conceptos,
            'medios' => $medios,
            'cuotas' => $cuotas,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha' => 'nullable|date',
            'id_venta' => 'required|exists:ventas,id_venta',
            'referencia_pago' => 'nullable|string|max:60',
            'id_concepto_pago' => 'nullable|exists:conceptos_pago,id_concepto_pago',
            'id_medio_pago' => 'nullable|exists:medios_pago,id_medio_pago',
            'descripcion' => 'nullable|string',
            'valor' => 'nullable|numeric|min:0',
            'id_cuota' => 'nullable|exists:planes_amortizacion_cuota,id_cuota',
        ]);

        Pago::create($validated);

        return redirect()->route('pagos.index')->with('success', 'Pago registrado exitosamente.');
    }

    public function show($id)
    {
        $pago = Pago::with(['venta.cliente', 'conceptoPago', 'medioPago', 'cuota'])->findOrFail($id);

        return Inertia::render('Ventas/Pago/Show', [
            'pago' => $pago,
        ]);
    }

    public function edit($id)
    {
        $pago = Pago::findOrFail($id);
        $ventas = Venta::with('cliente')->orderBy('fecha_venta', 'desc')->get();
        $conceptos = ConceptoPago::orderBy('concepto')->get();
        $medios = MedioPago::orderBy('medio_pago')->get();
        $cuotas = PlanAmortizacionCuota::with('planAmortizacion.venta')->get();

        return Inertia::render('Ventas/Pago/Edit', [
            'pago' => $pago,
            'ventas' => $ventas,
            'conceptos' => $conceptos,
            'medios' => $medios,
            'cuotas' => $cuotas,
        ]);
    }

    public function update(Request $request, $id)
    {
        $pago = Pago::findOrFail($id);

        $validated = $request->validate([
            'fecha' => 'nullable|date',
            'id_venta' => 'required|exists:ventas,id_venta',
            'referencia_pago' => 'nullable|string|max:60',
            'id_concepto_pago' => 'nullable|exists:conceptos_pago,id_concepto_pago',
            'id_medio_pago' => 'nullable|exists:medios_pago,id_medio_pago',
            'descripcion' => 'nullable|string',
            'valor' => 'nullable|numeric|min:0',
            'id_cuota' => 'nullable|exists:planes_amortizacion_cuota,id_cuota',
        ]);

        $pago->update($validated);

        return redirect()->route('pagos.show', $id)->with('success', 'Pago actualizado correctamente.');
    }

    public function destroy($id)
    {
        $pago = Pago::findOrFail($id);
        $pago->delete();

        return redirect()->route('pagos.index')->with('success', 'Pago eliminado correctamente.');
    }
}
