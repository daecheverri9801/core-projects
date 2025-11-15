<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\ConceptoPago;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ConceptoPagoWebController extends Controller
{
    public function index()
    {
        $conceptos = ConceptoPago::orderBy('concepto')->get();

        return Inertia::render('Ventas/ConceptoPago/Index', [
            'conceptos' => $conceptos,
        ]);
    }

    public function create()
    {
        return Inertia::render('Ventas/ConceptoPago/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'concepto' => 'required|string|max:80',
            'descripcion' => 'nullable|string|max:200',
        ]);

        ConceptoPago::create($validated);

        return redirect()->route('conceptos-pago.index')->with('success', 'Concepto de pago creado exitosamente.');
    }

    public function show($id)
    {
        $concepto = ConceptoPago::findOrFail($id);

        return Inertia::render('Ventas/ConceptoPago/Show', [
            'concepto' => $concepto,
        ]);
    }

    public function edit($id)
    {
        $concepto = ConceptoPago::findOrFail($id);

        return Inertia::render('Ventas/ConceptoPago/Edit', [
            'concepto' => $concepto,
        ]);
    }

    public function update(Request $request, $id)
    {
        $concepto = ConceptoPago::findOrFail($id);

        $validated = $request->validate([
            'concepto' => 'required|string|max:80',
            'descripcion' => 'nullable|string|max:200',
        ]);

        $concepto->update($validated);

        return redirect()->route('conceptos-pago.show', $id)->with('success', 'Concepto de pago actualizado correctamente.');
    }

    public function destroy($id)
    {
        $concepto = ConceptoPago::findOrFail($id);
        $concepto->delete();

        return redirect()->route('conceptos-pago.index')->with('success', 'Concepto de pago eliminado correctamente.');
    }
}
