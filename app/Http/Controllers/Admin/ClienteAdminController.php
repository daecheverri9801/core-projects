<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\TipoCliente;
use App\Models\TipoDocumento;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClienteAdminController extends Controller
{
    public function index()
    {
        $clientes = Cliente::with(['tipoCliente', 'tipoDocumento', 'ventas'])
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Admin/Cliente/Index', [
            'clientes' => $clientes,
        ]);
    }

    public function create()
    {
        $tiposCliente = TipoCliente::orderBy('tipo_cliente')->get();
        $tiposDocumento = TipoDocumento::orderBy('tipo_documento')->get();

        return Inertia::render('Admin/Cliente/Create', [
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
        ]);

        Cliente::create($validated);

        return redirect()->route('admin.clientes.index')->with('success', 'Cliente creado exitosamente.');
    }

    public function show($documento)
    {
        $cliente = Cliente::with(['tipoCliente', 'tipoDocumento', 'ventas'])
            ->where('documento', $documento)
            ->firstOrFail();

        return Inertia::render('Admin/Cliente/Show', [
            'cliente' => $cliente,
        ]);
    }

    public function edit($documento)
    {
        $cliente = Cliente::where('documento', $documento)->firstOrFail();
        $tiposCliente = TipoCliente::orderBy('tipo_cliente')->get();
        $tiposDocumento = TipoDocumento::orderBy('tipo_documento')->get();

        return Inertia::render('Admin/Cliente/Edit', [
            'cliente' => $cliente,
            'tiposCliente' => $tiposCliente,
            'tiposDocumento' => $tiposDocumento,
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

        return redirect()->route('admin.clientes.index', $documento)
            ->with('success', 'Cliente actualizado exitosamente');
    }

    public function destroy($documento)
    {
        $cliente = Cliente::where('documento', $documento)->firstOrFail();
        $cliente->delete();

        return redirect()->route('admin.clientes.index')->with('success', 'Cliente eliminado correctamente.');
    }
}
