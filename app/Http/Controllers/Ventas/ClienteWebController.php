<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\TipoCliente;
use App\Models\TipoDocumento;
use App\Models\Cargo;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClienteWebController extends Controller
{
    public function index(Request $request)
    {
        $empleado = $request->user()->load('cargo');

        $clientes = Cliente::with([
            'tipoCliente',
            'tipoDocumento',
            'ventas',
            'asesorResponsable:id_empleado,nombre,apellido'
        ])
            ->orderBy('nombre')
            ->get();

        $cargo = Cargo::whereIn('nombre',  ['Directora Comercial', 'Asesora Comercial'])->get();

        $asesores = $cargo->isNotEmpty()
            ? Empleado::whereIn('id_cargo', $cargo->pluck('id_cargo'))
            ->select('id_empleado', 'nombre', 'apellido')
            ->get() : collect();

        $tiposCliente = TipoCliente::query()
            ->orderBy('tipo_cliente')
            ->get(['id_tipo_cliente', 'tipo_cliente']);

        return Inertia::render('Ventas/Cliente/Index', [
            'clientes' => $clientes,
            'asesores' => $asesores,
            'tiposCliente' => $tiposCliente,
            'empleado' => $empleado,
        ]);
    }

    public function create(Request $request)
    {
        $empleado = $request->user()->load('cargo');
        $tiposCliente = TipoCliente::orderBy('tipo_cliente')->get();
        $tiposDocumento = TipoDocumento::orderBy('tipo_documento')->get();

        $asesores = Empleado::query()
            ->where('estado', true)
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get(['id_empleado', 'nombre', 'apellido']);

        return Inertia::render('Ventas/Cliente/Create', [
            'empleado' => $empleado,
            'tiposCliente' => $tiposCliente,
            'tiposDocumento' => $tiposDocumento,
            'asesores' => $asesores,
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
            'id_empleado_asesor' => 'nullable|exists:empleados,id_empleado',
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
            'id_empleado_asesor' => $validated['id_empleado_asesor'],
        ]);

        if (!empty($validated['redirect_to'])) {
            return redirect($validated['redirect_to'])
                ->with('success', 'Cliente creado exitosamente.')
                ->with('new_cliente_documento', $cliente->documento);
        }

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente creado exitosamente.');
    }

    public function show(Request $request, $documento)
    {
        $empleado = $request->user()->load('cargo');

        $cliente = Cliente::with([
            'tipoCliente',
            'tipoDocumento',
            'asesorResponsable:id_empleado,nombre,apellido',
            'bitacoras' => function ($q) {
                $q->with([
                    'empleado:id_empleado,nombre,apellido'
                ])->orderByDesc('fecha')->orderByDesc('created_at');
            },
            'ventas' => function ($q) {
                $q->with([
                    'proyecto:id_proyecto,nombre',
                    'apartamento:id_apartamento,numero',
                    'local:id_local,numero',
                ])->orderByDesc('fecha_venta');
            }
        ])
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

        $asesores = Empleado::query()
            ->where('estado', true)
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get(['id_empleado', 'nombre', 'apellido', 'email']);

        return Inertia::render('Ventas/Cliente/Edit', [
            'cliente' => $cliente,
            'tiposCliente' => $tiposCliente,
            'tiposDocumento' => $tiposDocumento,
            'asesores' => $asesores,
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
            'id_empleado_asesor' => 'required|exists:empleados,id_empleado',
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
