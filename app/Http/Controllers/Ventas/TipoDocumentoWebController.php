<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\TipoDocumento;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TipoDocumentoWebController extends Controller
{
    public function index()
    {
        $tipos = TipoDocumento::orderBy('tipo_documento')->get();

        return Inertia::render('Ventas/TipoDocumento/Index', [
            'tipos' => $tipos,
        ]);
    }

    public function create()
    {
        return Inertia::render('Ventas/TipoDocumento/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_documento' => 'required|string|max:60',
            'descripcion' => 'nullable|string|max:200',
        ]);

        TipoDocumento::create($validated);

        return redirect()->route('tipos-documento.index')->with('success', 'Tipo de documento creado exitosamente.');
    }

    public function show($id)
    {
        $tipo = TipoDocumento::findOrFail($id);

        return Inertia::render('Ventas/TipoDocumento/Show', [
            'tipo' => $tipo,
        ]);
    }

    public function edit($id)
    {
        $tipo = TipoDocumento::findOrFail($id);

        return Inertia::render('Ventas/TipoDocumento/Edit', [
            'tipo' => $tipo,
        ]);
    }

    public function update(Request $request, $id)
    {
        $tipo = TipoDocumento::findOrFail($id);

        $validated = $request->validate([
            'tipo_documento' => 'required|string|max:60',
            'descripcion' => 'nullable|string|max:200',
        ]);

        $tipo->update($validated);

        return redirect()->route('tipos-documento.show', $id)->with('success', 'Tipo de documento actualizado correctamente.');
    }

    public function destroy($id)
    {
        $tipo = TipoDocumento::findOrFail($id);
        $tipo->delete();

        return redirect()->route('tipos-documento.index')->with('success', 'Tipo de documento eliminado correctamente.');
    }
}
