<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\TipoCliente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TipoClienteWebController extends Controller
{
    public function index()
    {
        $tipos = TipoCliente::orderBy('tipo_cliente')->get();

        return Inertia::render('Ventas/TipoCliente/Index', [
            'tipos' => $tipos,
        ]);
    }

    public function create()
    {
        return Inertia::render('Ventas/TipoCliente/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_cliente' => 'required|string|max:60',
            'descripcion' => 'nullable|string|max:200',
        ]);

        TipoCliente::create($validated);

        return redirect()->route('tipos-cliente.index')->with('success', 'Tipo de cliente creado exitosamente.');
    }

    public function show($id)
    {
        $tipo = TipoCliente::findOrFail($id);

        return Inertia::render('Ventas/TipoCliente/Show', [
            'tipo' => $tipo,
        ]);
    }

    public function edit($id)
    {
        $tipo = TipoCliente::findOrFail($id);

        return Inertia::render('Ventas/TipoCliente/Edit', [
            'tipo' => $tipo,
        ]);
    }

    public function update(Request $request, $id)
    {
        $tipo = TipoCliente::findOrFail($id);

        $validated = $request->validate([
            'tipo_cliente' => 'required|string|max:60',
            'descripcion' => 'nullable|string|max:200',
        ]);

        $tipo->update($validated);

        return redirect()->route('tipos-cliente.show', $id)->with('success', 'Tipo de cliente actualizado correctamente.');
    }

    public function destroy($id)
    {
        $tipo = TipoCliente::findOrFail($id);
        $tipo->delete();

        return redirect()->route('tipos-cliente.index')->with('success', 'Tipo de cliente eliminado correctamente.');
    }
}
