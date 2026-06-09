<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\TipoCliente;
use App\Models\TipoDocumento;
use App\Models\Cargo;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'id_tipo_cliente' => 'nullable|exists:tipos_cliente,id_tipo_cliente',
            'id_tipo_documento' => 'nullable|exists:tipos_documento,id_tipo_documento',
            'documento' => 'nullable|numeric|unique:clientes,documento',
            'direccion' => 'nullable|string|max:200',
            'telefono' => 'required|numeric',
            'correo' => 'required|email|max:150',
            'id_empleado_asesor' => 'nullable|exists:empleados,id_empleado',
            'redirect_to' => 'nullable|string',
        ], [
            'documento.required' => 'El número de documento es obligatorio.',
            'documento.unique' => 'Ya existe un cliente registrado con este número de documento.',
            'documento.max' => 'El documento no puede tener más de 20 caracteres.',
            'nombre.required' => 'El nombre completo es obligatorio.',
            'telefono.required' => 'El número de teléfono es obligatorio.',
            'correo.required' => 'El correo electrónico es obligatorio.',
            'correo.email' => 'Debe ingresar un correo electrónico válido.',
            'id_tipo_documento.required' => 'Debe seleccionar un tipo de documento.',
            'id_tipo_cliente.required' => 'Debe seleccionar un tipo de cliente.',
            'id_empleado_asesor.required' => 'El asesor responsable es obligatorio.',
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
        $cliente = Cliente::with('asesorResponsable')
            ->where('documento', $documento)
            ->firstOrFail();
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

    // public function update(Request $request, $documento)
    // {
    //     $cliente = Cliente::where('documento', $documento)->firstOrFail();

    //     // Obtener el empleado que está realizando la edición
    //     $empleadoActual = $request->user();


    //     $validated = $request->validate([
    //         'nombre' => 'required|string|max:255',
    //         'id_tipo_cliente' => 'nullable|exists:tipos_cliente,id_tipo_cliente',
    //         'id_tipo_documento' => 'nullable|exists:tipos_documento,id_tipo_documento',
    //         'documento' => 'required|numeric|unique:clientes,documento,' . $cliente->id_cliente . ',id_cliente',
    //         'direccion' => 'nullable|string|max:255',
    //         'telefono' => 'required|numeric',
    //         'correo' => 'required|email|max:255',
    //         'id_empleado_asesor' => 'nullable|exists:empleados,id_empleado',
    //     ], [
    //         'documento.unique' => 'Ya existe un cliente registrado con este número de documento.',
    //         'documento.max' => 'El documento no puede tener más de 20 caracteres.',
    //         'nombre.required' => 'El nombre completo es obligatorio.',
    //         'telefono.required' => 'El número de teléfono es obligatorio.',
    //         'correo.required' => 'El correo electrónico es obligatorio.',
    //         'correo.email' => 'Debe ingresar un correo electrónico válido.',
    //         'id_tipo_documento.required' => 'Debe seleccionar un tipo de documento.',
    //         'id_tipo_cliente.required' => 'Debe seleccionar un tipo de cliente.',
    //         'id_empleado_asesor.required' => 'El asesor responsable es obligatorio.',
    //     ]);

    //     // Lógica para asignar asesor solo si no tiene uno asignado
    //     if (is_null($cliente->id_empleado_asesor)) {
    //         // Si el cliente no tiene asesor asignado, se le asigna el empleado que está editando
    //         $validated['id_empleado_asesor'] = $empleadoActual->id_empleado;
    //     } else {
    //         // Si ya tiene asesor, mantenemos el que ya tenía (ignoramos lo que venga del formulario)
    //         $validated['id_empleado_asesor'] = $cliente->id_empleado_asesor;
    //     }

    //     $cliente->update($validated);

    //     return redirect()->route('clientes.index')
    //         ->with('success', 'Cliente actualizado exitosamente');
    // }

    public function update(Request $request, $documento)
    {
        $cliente = Cliente::where('documento', $documento)->firstOrFail();
        $empleadoActual = $request->user();

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'id_tipo_cliente' => 'nullable|exists:tipos_cliente,id_tipo_cliente',
            'id_tipo_documento' => 'nullable|exists:tipos_documento,id_tipo_documento',
            'documento' => 'required|numeric|unique:clientes,documento,' . $cliente->id_cliente . ',id_cliente',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'required|numeric',
            'correo' => 'required|email|max:255',
            'id_empleado_asesor' => 'nullable|exists:empleados,id_empleado',
        ], [
            'documento.unique' => 'Ya existe un cliente registrado con este número de documento.',
            'nombre.required' => 'El nombre completo es obligatorio.',
            'telefono.required' => 'El número de teléfono es obligatorio.',
            'correo.required' => 'El correo electrónico es obligatorio.',
            'correo.email' => 'Debe ingresar un correo electrónico válido.',
        ]);

        // Lógica de asesor
        if (is_null($cliente->id_empleado_asesor)) {
            $validated['id_empleado_asesor'] = $empleadoActual->id_empleado;
        } else {
            $validated['id_empleado_asesor'] = $cliente->id_empleado_asesor;
        }

        // Actualizar cliente (incluyendo el documento)
        $cliente->update($validated);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente actualizado exitosamente');
    }

    public function destroy($documento)
    {
        $cliente = Cliente::where('documento', $documento)->firstOrFail();
        $cliente->delete();

        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado correctamente.');
    }
}
