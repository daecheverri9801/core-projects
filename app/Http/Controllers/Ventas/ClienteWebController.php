<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\TipoCliente;
use App\Models\TipoDocumento;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClienteWebController extends Controller
{
    public function index(Request $request)
    {
        $empleado = $request->user()->load('cargo');
        $clientes = Cliente::with(['tipoCliente', 'tipoDocumento', 'ventas'])
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Ventas/Cliente/Index', [
            'clientes' => $clientes,
            'empleado' => $empleado,
        ]);
    }

    public function create(Request $request)
    {
        $empleado = $request->user()->load('cargo');
        $tiposCliente = TipoCliente::orderBy('tipo_cliente')->get();
        $tiposDocumento = TipoDocumento::orderBy('tipo_documento')->get();

        return Inertia::render('Ventas/Cliente/Create', [
            'empleado' => $empleado,
            'tiposCliente' => $tiposCliente,
            'tiposDocumento' => $tiposDocumento,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'id_tipo_cliente' => 'required|exists:tipos_cliente,id_tipo_cliente',
            'id_tipo_documento' => 'required|exists:tipos_documento,id_tipo_documento',
            'documento' => 'required|string|max:40|unique:clientes,documento',
            'direccion' => 'nullable|string|max:200',
            'telefono' => 'nullable|string|max:30',
            'correo' => 'nullable|email|max:150',
            'redirect_to' => 'nullable|string',
        ]);

        $cliente = Cliente::create([
            'nombre' => $validated['nombre'],
            'id_tipo_cliente' => $validated['id_tipo_cliente'],
            'id_tipo_documento' => $validated['id_tipo_documento'],
            'documento' => $validated['documento'],
            'direccion' => $validated['direccion'] ?? null,
            'telefono' => $validated['telefono'] ?? null,
            'correo' => $validated['correo'] ?? null,
        ]);

        // si viene de ventas/create, vuelves allí y mandas el documento para seleccionarlo
        $redirectTo = $validated['redirect_to'] ?? url()->previous();

        return redirect($redirectTo)
            ->with('success', 'Cliente creado exitosamente.')
            ->with('new_cliente_documento', $cliente->documento);
    }

    public function show(Request $request, $documento)
    {
        $empleado = $request->user()->load('cargo');
        $cliente = Cliente::with(['tipoCliente', 'tipoDocumento', 'ventas'])
            ->where('documento', $documento)
            ->firstOrFail();

        return Inertia::render('Ventas/Cliente/Show', [
            'cliente' => $cliente,
            'empleado' => $empleado,
        ]);
    }

    public function edit(Request $request, $documento)
    {
        $empleado = $request->user()->load('cargo');
        $cliente = Cliente::where('documento', $documento)->firstOrFail();
        $tiposCliente = TipoCliente::orderBy('tipo_cliente')->get();
        $tiposDocumento = TipoDocumento::orderBy('tipo_documento')->get();

        return Inertia::render('Ventas/Cliente/Edit', [
            'cliente' => $cliente,
            'tiposCliente' => $tiposCliente,
            'tiposDocumento' => $tiposDocumento,
            'empleado' => $empleado,
        ]);
    }

    public function update(Request $request, $documento)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'id_tipo_cliente' => 'required|exists:tipos_cliente,id_tipo_cliente',
            'id_tipo_documento' => 'required|exists:tipos_documento,id_tipo_documento',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:255',
        ]);

        $cliente = Cliente::where('documento', $documento)->firstOrFail();
        $cliente->update($validated);

        return redirect()->route('clientes.show', $documento)
            ->with('success', 'Cliente actualizado exitosamente');
    }

    public function destroy($documento)
    {
        $cliente = Cliente::where('documento', $documento)->firstOrFail();
        $cliente->delete();

        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado correctamente.');
    }
}
