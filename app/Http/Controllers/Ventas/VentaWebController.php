<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Apartamento;
use App\Models\Local;
use App\Models\Proyecto;
use App\Models\FormaPago;
use App\Models\EstadoVenta;
use App\Models\EstadoInmueble;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VentaWebController extends Controller
{
    public function index()
    {
        $ventas = Venta::with([
            'cliente',
            'empleado',
            'apartamento.estadoInmueble',
            'local.estadoInmueble',
            'proyecto',
            'formaPago',
        ])->orderBy('fecha_venta', 'desc')->get();

        return Inertia::render('Ventas/Venta/Index', [
            'ventas' => $ventas,
        ]);
    }

    

    public function create(Request $request)
    {
        $clientes = Cliente::orderBy('nombre')->get();
        $empleados = Empleado::orderBy('nombre')->get();
        $apartamentos = Apartamento::with(['torre.proyecto', 'estadoInmueble'])
            ->where('id_estado_inmueble', 1)
            ->get();
        $locales = Local::with(['torre.proyecto', 'estadoInmueble'])
            ->where('id_estado_inmueble', 1)
            ->get();
        $proyectos = Proyecto::orderBy('nombre')->get();
        $formasPago = FormaPago::orderBy('forma_pago')->get();
        $estadosInmueble = EstadoInmueble::orderBy('estados_inmueble')->get();

        $inmueblePrecargado = null;
        if ($request->has('inmueble_tipo') && $request->has('inmueble_id')) {
            if ($request->inmueble_tipo === 'apartamento') {
                $inmueblePrecargado = Apartamento::with(['torre', 'estadoInmueble'])
                    ->find($request->inmueble_id);
            } else if ($request->inmueble_tipo === 'local') {
                $inmueblePrecargado = Local::with(['torre', 'estadoInmueble'])
                    ->find($request->inmueble_id);
            }
        }

        return Inertia::render('Ventas/Venta/Create', [
            'clientes' => $clientes,
            'empleados' => $empleados,
            'apartamentos' => $apartamentos,
            'locales' => $locales,
            'proyectos' => $proyectos,
            'formasPago' => $formasPago,
            'estadosInmueble' => $estadosInmueble,
            'inmueblePrecargado' => $inmueblePrecargado,
        ]);
    }

    public function store(Request $request)
    {
        // 🧪 DEBUG
        \Log::info('📥 Datos recibidos:', $request->all());

        $validated = $request->validate([
            'id_empleado' => 'required|exists:empleados,id_empleado',
            'documento_cliente' => 'required|exists:clientes,documento',
            'fecha_venta' => 'required|date',
            'fecha_vencimiento' => 'nullable|date',
            'inmueble_tipo' => 'required|in:apartamento,local',
            'inmueble_id' => 'required|integer',
            'id_proyecto' => 'nullable|exists:proyectos,id_proyecto',
            'id_forma_pago' => 'required|exists:formas_pago,id_forma_pago',
            'id_estado_inmueble' => 'required|exists:estados_inmueble,id_estado_inmueble',
            'cuota_inicial' => 'nullable|numeric|min:0',
            'valor_restante' => 'nullable|numeric|min:0',
            'descripcion' => 'nullable|string|max:300',
            'valor_base' => 'nullable|numeric|min:0',
            'iva' => 'nullable|numeric|min:0',
            'valor_total' => 'nullable|numeric|min:0',
        ]);

        // ✅ Determinar el inmueble y asignar IDs
        if ($validated['inmueble_tipo'] === 'apartamento') {
            $validated['id_apartamento'] = $validated['inmueble_id'];
            $validated['id_local'] = null;
            $inmueble = Apartamento::findOrFail($validated['inmueble_id']);
        } else {
            $validated['id_local'] = $validated['inmueble_id'];
            $validated['id_apartamento'] = null;
            $inmueble = Local::findOrFail($validated['inmueble_id']);
        }

        // Remover campos temporales que no existen en la tabla ventas
        unset($validated['inmueble_tipo']);
        unset($validated['inmueble_id']);

        // ✅ Crear la venta
        $venta = Venta::create($validated);

        // ✅ SINCRONIZAR: Actualizar el estado del inmueble
        $inmueble->update(['id_estado_inmueble' => $validated['id_estado_inmueble']]);

        return redirect()->route('ventas.index')->with('success', 'Venta creada exitosamente.');
    }

    public function show($id)
    {
        $venta = Venta::with([
            'cliente:nombre',
            'empleado',
            'apartamento.estadoInmueble',
            'local.estadoInmueble',
            'proyecto',
            'formaPago',
            'planAmortizacion.cuotas',
            'pagos'
        ])->findOrFail($id);

        return Inertia::render('Ventas/Venta/Show', [
            'venta' => $venta,
        ]);
    }

    public function edit($id)
    {
        $venta = Venta::with([
            'apartamento.estadoInmueble',
            'local.estadoInmueble'
        ])->findOrFail($id);

        $clientes = Cliente::orderBy('nombre')->get();
        $empleados = Empleado::orderBy('nombre')->get();
        $apartamentos = Apartamento::with(['torre.proyecto', 'estadoInmueble'])->get();
        $locales = Local::with(['torre.proyecto', 'estadoInmueble'])->get();
        $proyectos = Proyecto::orderBy('nombre')->get();
        $formasPago = FormaPago::orderBy('forma_pago')->get();
        $estadosInmueble = EstadoInmueble::orderBy('nombre')->get();

        return Inertia::render('Ventas/Venta/Edit', [
            'venta' => $venta,
            'clientes' => $clientes,
            'empleados' => $empleados,
            'apartamentos' => $apartamentos,
            'locales' => $locales,
            'proyectos' => $proyectos,
            'formasPago' => $formasPago,
            'estadosInmueble' => $estadosInmueble,
        ]);
    }

    public function update(Request $request, $id)
    {
        $venta = Venta::findOrFail($id);

        $validated = $request->validate([
            'id_empleado' => 'required|exists:empleados,id_empleado',
            'documento_cliente' => 'required|exists:clientes,documento',
            'fecha_venta' => 'required|date',
            'fecha_vencimiento' => 'nullable|date',
            'inmueble_tipo' => 'required|in:apartamento,local',
            'inmueble_id' => 'required|integer',
            'id_proyecto' => 'nullable|exists:proyectos,id_proyecto',
            'id_forma_pago' => 'required|exists:formas_pago,id_forma_pago',
            'id_estado_inmueble' => 'required|exists:estados_inmueble,id_estado_inmueble',
            'cuota_inicial' => 'nullable|numeric|min:0',
            'valor_restante' => 'nullable|numeric|min:0',
            'descripcion' => 'nullable|string|max:300',
            'valor_base' => 'nullable|numeric|min:0',
            'iva' => 'nullable|numeric|min:0',
            'valor_total' => 'nullable|numeric|min:0',
        ]);

        // ✅ Liberar el inmueble anterior si cambió
        $inmuebleAnterior = null;
        if ($venta->id_apartamento) {
            $inmuebleAnterior = Apartamento::find($venta->id_apartamento);
        } elseif ($venta->id_local) {
            $inmuebleAnterior = Local::find($venta->id_local);
        }

        // ✅ Determinar el nuevo inmueble
        if ($validated['inmueble_tipo'] === 'apartamento') {
            $validated['id_apartamento'] = $validated['inmueble_id'];
            $validated['id_local'] = null;
            $inmuebleNuevo = Apartamento::findOrFail($validated['inmueble_id']);
        } else {
            $validated['id_local'] = $validated['inmueble_id'];
            $validated['id_apartamento'] = null;
            $inmuebleNuevo = Local::findOrFail($validated['inmueble_id']);
        }

        // Remover campos temporales
        unset($validated['inmueble_tipo']);
        unset($validated['inmueble_id']);

        // ✅ Actualizar la venta (NO crear una nueva)
        $venta->update($validated);

        // ✅ SINCRONIZAR: Liberar inmueble anterior si cambió
        if ($inmuebleAnterior && $inmuebleAnterior->getKey() !== $inmuebleNuevo->getKey()) {
            $inmuebleAnterior->update(['id_estado_inmueble' => 1]); // 1 = Disponible
        }

        // ✅ SINCRONIZAR: Actualizar estado del nuevo inmueble
        $inmuebleNuevo->update(['id_estado_inmueble' => $validated['id_estado_inmueble']]);

        return redirect()->route('ventas.show', $id)->with('success', 'Venta actualizada correctamente.');
    }

    public function destroy($id)
    {
        $venta = Venta::findOrFail($id);

        // ✅ SINCRONIZAR: Liberar el inmueble al eliminar la venta
        if ($venta->id_apartamento) {
            $inmueble = Apartamento::find($venta->id_apartamento);
            if ($inmueble) {
                $inmueble->update(['id_estado_inmueble' => 1]); // 1 = Disponible
            }
        } elseif ($venta->id_local) {
            $inmueble = Local::find($venta->id_local);
            if ($inmueble) {
                $inmueble->update(['id_estado_inmueble' => 1]); // 1 = Disponible
            }
        }

        $venta->delete();

        return redirect()->route('ventas.index')->with('success', 'Venta eliminada correctamente.');
    }
}
