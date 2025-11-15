<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\EstadoVenta;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EstadoVentaWebController extends Controller
{
    public function index()
    {
        $estados = EstadoVenta::orderBy('estado_venta')->get();

        return Inertia::render('Ventas/EstadoVenta/Index', [
            'estados' => $estados,
        ]);
    }

    public function create()
    {
        return Inertia::render('Ventas/EstadoVenta/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'estado_venta' => 'required|string|max:40',
            'descripcion' => 'nullable|string|max:200',
        ]);

        EstadoVenta::create($validated);

        return redirect()->route('estados-venta.index')->with('success', 'Estado de venta creado exitosamente.');
    }

    public function show($id)
    {
        $estado = EstadoVenta::findOrFail($id);

        return Inertia::render('Ventas/EstadoVenta/Show', [
            'estado' => $estado,
        ]);
    }

    public function edit($id)
    {
        $estado = EstadoVenta::findOrFail($id);

        return Inertia::render('Ventas/EstadoVenta/Edit', [
            'estado' => $estado,
        ]);
    }

    public function update(Request $request, $id)
    {
        $estado = EstadoVenta::findOrFail($id);

        $validated = $request->validate([
            'estado_venta' => 'required|string|max:40',
            'descripcion' => 'nullable|string|max:200',
        ]);

        $estado->update($validated);

        return redirect()->route('estados-venta.show', $id)->with('success', 'Estado de venta actualizado correctamente.');
    }

    public function destroy($id)
    {
        $estado = EstadoVenta::findOrFail($id);
        $estado->delete();

        return redirect()->route('estados-venta.index')->with('success', 'Estado de venta eliminado correctamente.');
    }
}
