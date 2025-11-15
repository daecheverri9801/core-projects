<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\FormaPago;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FormaPagoWebController extends Controller
{
    public function index()
    {
        $formas = FormaPago::orderBy('forma_pago')->get();

        return Inertia::render('Ventas/FormaPago/Index', [
            'formas' => $formas,
        ]);
    }

    public function create()
    {
        return Inertia::render('Ventas/FormaPago/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'forma_pago' => 'required|string|max:60',
            'descripcion' => 'nullable|string|max:200',
        ]);

        FormaPago::create($validated);

        return redirect()->route('formas-pago.index')->with('success', 'Forma de pago creada exitosamente.');
    }

    public function show($id)
    {
        $forma = FormaPago::findOrFail($id);

        return Inertia::render('Ventas/FormaPago/Show', [
            'forma' => $forma,
        ]);
    }

    public function edit($id)
    {
        $forma = FormaPago::findOrFail($id);

        return Inertia::render('Ventas/FormaPago/Edit', [
            'forma' => $forma,
        ]);
    }

    public function update(Request $request, $id)
    {
        $forma = FormaPago::findOrFail($id);

        $validated = $request->validate([
            'forma_pago' => 'required|string|max:60',
            'descripcion' => 'nullable|string|max:200',
        ]);

        $forma->update($validated);

        return redirect()->route('formas-pago.show', $id)->with('success', 'Forma de pago actualizada correctamente.');
    }

    public function destroy($id)
    {
        $forma = FormaPago::findOrFail($id);
        $forma->delete();

        return redirect()->route('formas-pago.index')->with('success', 'Forma de pago eliminada correctamente.');
    }
}
