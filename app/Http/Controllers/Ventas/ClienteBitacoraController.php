<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\ClienteBitacora;
use Illuminate\Http\Request;

class ClienteBitacoraController extends Controller
{
    public function store(Request $request, $documento)
    {
        $cliente = Cliente::where('documento', $documento)->firstOrFail();

        $validated = $request->validate([
            'fecha' => 'required|date',
            'comentario' => 'required|string|max:5000',
        ]);

        ClienteBitacora::create([
            'documento_cliente' => $cliente->documento,
            'id_empleado' => $request->user()->id_empleado,
            'fecha' => $validated['fecha'],
            'comentario' => $validated['comentario'],
        ]);

        return redirect()
            ->route('clientes.show', $cliente->documento)
            ->with('success', 'Seguimiento registrado correctamente.');
    }

    public function update(Request $request, $documento, $idBitacora)
    {
        $cliente = Cliente::where('documento', $documento)->firstOrFail();

        $bitacora = ClienteBitacora::where('id_bitacora_cliente', $idBitacora)
            ->where('documento_cliente', $cliente->documento)
            ->firstOrFail();

        $validated = $request->validate([
            'fecha' => 'required|date',
            'comentario' => 'required|string|max:5000',
        ]);

        $bitacora->update([
            'fecha' => $validated['fecha'],
            'comentario' => $validated['comentario'],
        ]);

        return redirect()
            ->route('clientes.show', $cliente->documento)
            ->with('success', 'Seguimiento actualizado correctamente.');
    }

    public function destroy($documento, $idBitacora)
    {
        $cliente = Cliente::where('documento', $documento)->firstOrFail();

        $bitacora = ClienteBitacora::where('id_bitacora_cliente', $idBitacora)
            ->where('documento_cliente', $cliente->documento)
            ->firstOrFail();

        $bitacora->delete();

        return redirect()
            ->route('clientes.show', $cliente->documento)
            ->with('success', 'Seguimiento eliminado correctamente.');
    }
}
