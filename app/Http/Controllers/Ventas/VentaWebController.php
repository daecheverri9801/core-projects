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
        ])
            ->orderBy('fecha_venta', 'desc')
            ->get();

        return Inertia::render('Ventas/Venta/Index', [
            'ventas' => $ventas,
        ]);
    }

    public function create(Request $request)
    {
        $clientes = Cliente::orderBy('nombre')->get();
        $empleados = Empleado::orderBy('nombre')->get();

        // Solo inmuebles DISPONIBLES
        $apartamentos = Apartamento::with(['torre.proyecto', 'estadoInmueble'])
            ->where('id_estado_inmueble', 1) // 1 = Disponible
            ->get();

        $locales = Local::with(['torre.proyecto', 'estadoInmueble'])
            ->where('id_estado_inmueble', 1) // 1 = Disponible
            ->get();

        $proyectos = Proyecto::orderBy('nombre')->get();
        $formasPago = FormaPago::orderBy('forma_pago')->get();

        // CORREGIDO: nombre de columna en estados_inmueble
        $estadosInmueble = EstadoInmueble::orderBy('nombre')->get();

        // Inmueble precargado desde Catálogo
        $inmueblePrecargado = null;
        if ($request->has('inmueble_tipo') && $request->has('inmueble_id')) {
            if ($request->inmueble_tipo === 'apartamento') {
                $inmueblePrecargado = Apartamento::with(['torre', 'estadoInmueble'])
                    ->find($request->inmueble_id);
            } elseif ($request->inmueble_tipo === 'local') {
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
        \Log::info('📥 Datos recibidos en ventas.store:', $request->all());

        $validated = $request->validate([
            'id_empleado'        => 'required|exists:empleados,id_empleado',
            'documento_cliente'  => 'required|exists:clientes,documento',
            'fecha_venta'        => 'required|date',
            'fecha_vencimiento'  => 'nullable|date',
            'inmueble_tipo'      => 'required|in:apartamento,local',
            'inmueble_id'        => 'required|integer',
            'id_proyecto'        => 'nullable|exists:proyectos,id_proyecto',
            'id_forma_pago'      => 'required|exists:formas_pago,id_forma_pago',
            'id_estado_inmueble' => 'required|exists:estados_inmueble,id_estado_inmueble',
            'cuota_inicial'      => 'nullable|numeric|min:0',
            'valor_restante'     => 'nullable|numeric|min:0',
            'descripcion'        => 'nullable|string|max:300',
            'valor_base'         => 'nullable|numeric|min:0',
            'iva'                => 'nullable|numeric|min:0',
            'valor_total'        => 'nullable|numeric|min:0',
        ]);

        // Determinar inmueble y validar disponibilidad
        if ($validated['inmueble_tipo'] === 'apartamento') {
            $inmueble = Apartamento::with('torre')->findOrFail($validated['inmueble_id']);

            // Validar que el inmueble esté disponible
            if ($inmueble->id_estado_inmueble !== 1) {
                return back()
                    ->withErrors(['inmueble_id' => 'El apartamento seleccionado ya no está disponible.'])
                    ->withInput();
            }

            $validated['id_apartamento'] = $validated['inmueble_id'];
            $validated['id_local'] = null;
        } else {
            $inmueble = Local::with('torre')->findOrFail($validated['inmueble_id']);

            if ($inmueble->id_estado_inmueble !== 1) {
                return back()
                    ->withErrors(['inmueble_id' => 'El local seleccionado ya no está disponible.'])
                    ->withInput();
            }

            $validated['id_local'] = $validated['inmueble_id'];
            $validated['id_apartamento'] = null;
        }

        // Ajustar id_proyecto desde el inmueble si no viene
        if (empty($validated['id_proyecto']) && $inmueble->torre) {
            $validated['id_proyecto'] = $inmueble->torre->id_proyecto ?? null;
        }

        // Normalizar valores económicos
        $validated['valor_base'] = $validated['valor_base'] ?? $validated['valor_total'] ?? 0;
        $validated['iva'] = $validated['iva'] ?? 0;
        $validated['valor_total'] = $validated['valor_total'] ?? $validated['valor_base'] + $validated['iva'];

        // Si no llega valor_restante, calcularlo
        if (!array_key_exists('valor_restante', $validated) || $validated['valor_restante'] === null) {
            $cuota = $validated['cuota_inicial'] ?? 0;
            $validated['valor_restante'] = max(0, $validated['valor_total'] - $cuota);
        }

        // Remover auxiliares que no existen en la tabla ventas
        unset($validated['inmueble_tipo'], $validated['inmueble_id']);

        // Crear venta
        $venta = Venta::create($validated);

        // SINCRONIZAR: actualizar estado (y propietario en apartamento)
        $updateInmueble = [
            'id_estado_inmueble' => $validated['id_estado_inmueble'],
        ];

        if ($inmueble instanceof Apartamento) {
            $updateInmueble['documento'] = $validated['documento_cliente'];
        }

        $inmueble->update($updateInmueble);

        return redirect()
            ->route('ventas.index')
            ->with('success', 'Venta creada exitosamente.');
    }

    public function show($id)
    {
        $venta = Venta::with([
            'cliente',
            'empleado',
            'apartamento.estadoInmueble',
            'local.estadoInmueble',
            'proyecto',
            'formaPago',
            'planAmortizacion.cuotas',
            'pagos',
        ])->findOrFail($id);

        return Inertia::render('Ventas/Venta/Show', [
            'venta' => $venta,
        ]);
    }

    public function edit($id)
    {
        $venta = Venta::with([
            'apartamento.estadoInmueble',
            'local.estadoInmueble',
        ])->findOrFail($id);

        $clientes = Cliente::orderBy('nombre')->get();
        $empleados = Empleado::orderBy('nombre')->get();

        // Inmuebles disponibles + el actualmente asociado
        $apartamentos = Apartamento::with(['torre.proyecto', 'estadoInmueble'])
            ->where(function ($q) use ($venta) {
                $q->where('id_estado_inmueble', 1); // disponibles
                if ($venta->id_apartamento) {
                    $q->orWhere('id_apartamento', $venta->id_apartamento);
                }
            })
            ->get();

        $locales = Local::with(['torre.proyecto', 'estadoInmueble'])
            ->where(function ($q) use ($venta) {
                $q->where('id_estado_inmueble', 1); // disponibles
                if ($venta->id_local) {
                    $q->orWhere('id_local', $venta->id_local);
                }
            })
            ->get();

        $proyectos = Proyecto::orderBy('nombre')->get();
        $formasPago = FormaPago::orderBy('forma_pago')->get();
        $estadosInmueble = EstadoInmueble::orderBy('nombre')->get();

        return Inertia::render('Ventas/Venta/Edit', [
            'venta'          => $venta,
            'clientes'       => $clientes,
            'empleados'      => $empleados,
            'apartamentos'   => $apartamentos,
            'locales'        => $locales,
            'proyectos'      => $proyectos,
            'formasPago'     => $formasPago,
            'estadosInmueble' => $estadosInmueble,
        ]);
    }

    public function update(Request $request, $id)
    {
        $venta = Venta::findOrFail($id);

        $validated = $request->validate([
            'id_empleado'        => 'required|exists:empleados,id_empleado',
            'documento_cliente'  => 'required|exists:clientes,documento',
            'fecha_venta'        => 'required|date',
            'fecha_vencimiento'  => 'nullable|date',
            'inmueble_tipo'      => 'required|in:apartamento,local',
            'inmueble_id'        => 'required|integer',
            'id_proyecto'        => 'nullable|exists:proyectos,id_proyecto',
            'id_forma_pago'      => 'required|exists:formas_pago,id_forma_pago',
            'id_estado_inmueble' => 'required|exists:estados_inmueble,id_estado_inmueble',
            'cuota_inicial'      => 'nullable|numeric|min:0',
            'valor_restante'     => 'nullable|numeric|min:0',
            'descripcion'        => 'nullable|string|max:300',
            'valor_base'         => 'nullable|numeric|min:0',
            'iva'                => 'nullable|numeric|min:0',
            'valor_total'        => 'nullable|numeric|min:0',
        ]);

        // Inmueble anterior
        $inmuebleAnterior = null;
        if ($venta->id_apartamento) {
            $inmuebleAnterior = Apartamento::find($venta->id_apartamento);
        } elseif ($venta->id_local) {
            $inmuebleAnterior = Local::find($venta->id_local);
        }

        // Determinar nuevo inmueble y validar disponibilidad
        if ($validated['inmueble_tipo'] === 'apartamento') {
            $inmuebleNuevo = Apartamento::with('torre')->findOrFail($validated['inmueble_id']);

            // Si es distinto al anterior, validar que esté disponible
            if (!$inmuebleAnterior || $inmuebleNuevo->id_apartamento !== $inmuebleAnterior->id_apartamento) {
                if ($inmuebleNuevo->id_estado_inmueble !== 1) {
                    return back()
                        ->withErrors(['inmueble_id' => 'El apartamento seleccionado ya no está disponible.'])
                        ->withInput();
                }
            }

            $validated['id_apartamento'] = $validated['inmueble_id'];
            $validated['id_local'] = null;
        } else {
            $inmuebleNuevo = Local::with('torre')->findOrFail($validated['inmueble_id']);

            if (!$inmuebleAnterior || $inmuebleNuevo->id_local !== $inmuebleAnterior->id_local) {
                if ($inmuebleNuevo->id_estado_inmueble !== 1) {
                    return back()
                        ->withErrors(['inmueble_id' => 'El local seleccionado ya no está disponible.'])
                        ->withInput();
                }
            }

            $validated['id_local'] = $validated['inmueble_id'];
            $validated['id_apartamento'] = null;
        }

        // Ajustar id_proyecto desde el inmueble si no viene
        if (empty($validated['id_proyecto']) && $inmuebleNuevo->torre) {
            $validated['id_proyecto'] = $inmuebleNuevo->torre->id_proyecto ?? null;
        }

        // Normalizar valores económicos
        $validated['valor_base'] = $validated['valor_base'] ?? $validated['valor_total'] ?? 0;
        $validated['iva'] = $validated['iva'] ?? 0;
        $validated['valor_total'] = $validated['valor_total'] ?? $validated['valor_base'] + $validated['iva'];

        if (!array_key_exists('valor_restante', $validated) || $validated['valor_restante'] === null) {
            $cuota = $validated['cuota_inicial'] ?? 0;
            $validated['valor_restante'] = max(0, $validated['valor_total'] - $cuota);
        }

        // Remover auxiliares
        unset($validated['inmueble_tipo'], $validated['inmueble_id']);

        // Actualizar venta
        $venta->update($validated);

        // Liberar inmueble anterior si cambió
        if ($inmuebleAnterior && $inmuebleNuevo->getKey() !== $inmuebleAnterior->getKey()) {
            $dataLiberar = [
                'id_estado_inmueble' => 1, // Disponible
            ];
            if ($inmuebleAnterior instanceof Apartamento) {
                $dataLiberar['documento'] = null;
            }
            $inmuebleAnterior->update($dataLiberar);
        }

        // Actualizar estado y propietario en el nuevo inmueble
        $dataNuevo = [
            'id_estado_inmueble' => $validated['id_estado_inmueble'],
        ];
        if ($inmuebleNuevo instanceof Apartamento) {
            $dataNuevo['documento'] = $validated['documento_cliente'];
        }
        $inmuebleNuevo->update($dataNuevo);

        return redirect()
            ->route('ventas.show', $id)
            ->with('success', 'Venta actualizada correctamente.');
    }

    public function destroy($id)
    {
        $venta = Venta::findOrFail($id);

        // Liberar inmueble asociado
        if ($venta->id_apartamento) {
            $inmueble = Apartamento::find($venta->id_apartamento);
            if ($inmueble) {
                $data = ['id_estado_inmueble' => 1]; // Disponible
                // Limpiar propietario
                if (property_exists($inmueble, 'documento') || array_key_exists('documento', $inmueble->getAttributes())) {
                    $data['documento'] = null;
                }
                $inmueble->update($data);
            }
        } elseif ($venta->id_local) {
            $inmueble = Local::find($venta->id_local);
            if ($inmueble) {
                $inmueble->update(['id_estado_inmueble' => 1]); // Disponible
            }
        }

        $venta->delete();

        return redirect()
            ->route('ventas.index')
            ->with('success', 'Venta eliminada correctamente.');
    }
}
