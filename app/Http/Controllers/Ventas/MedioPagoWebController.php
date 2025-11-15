<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\MedioPago;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MedioPagoWebController extends Controller
{
    public function index()
    {
        $medios = MedioPago::orderBy('medio_pago')->get();

        return Inertia::render('Ventas/MedioPago/Index', [
            'medios' => $medios,
        ]);
    }

    public function create()
    {
        return Inertia::render('Ventas/MedioPago/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'medio_pago' => 'required|string|max:60',
            'descripcion' => 'nullable|string|max:200',
        ]);

        MedioPago::create($validated);

        return redirect()->route('medios-pago.index')->with('success', 'Medio de pago creado exitosamente.');
    }

    public function show($id)
    {
        $medio = MedioPago::findOrFail($id);

        return Inertia::render('Ventas/MedioPago/Show', [
            'medio' => $medio,
        ]);
    }

    public function edit($id)
    {
        $medio = MedioPago::findOrFail($id);

        return Inertia::render('Ventas/MedioPago/Edit', [
            'medio' => $medio,
        ]);
    }

    public function update(Request $request, $id)
    {
        $medio = MedioPago::findOrFail($id);

        $validated = $request->validate([
            'medio_pago' => 'required|string|max:60',
            'descripcion' => 'nullable|string|max:200',
        ]);

        $medio->update($validated);

        return redirect()->route('medios-pago.show', $id)->with('success', 'Medio de pago actualizado correctamente.');
    }

    public function destroy($id)
    {
        $medio = MedioPago::findOrFail($id);
        $medio->delete();

        return redirect()->route('medios-pago.index')->with('success', 'Medio de pago eliminado correctamente.');
    }
}
