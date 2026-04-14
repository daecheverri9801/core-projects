<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\ConceptoPago;
use App\Models\MedioPago;
use App\Models\Pago;
use App\Models\PlanAmortizacionCuota;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PagoWebController extends Controller
{
    public function index(Request $request)
    {
        $empleado = $request->user()->load('cargo');

        $pagos = Pago::with([
            'venta.cliente',
            'venta.proyecto',
            'venta.apartamento',
            'venta.local',
            'conceptoPago',
            'medioPago',
            'cuota.planAmortizacion',
        ])
            ->orderBy('fecha', 'desc')
            ->get()
            ->map(function ($pago) {
                $venta = $pago->venta;

                $inmueble = '—';
                if ($venta?->apartamento) {
                    $inmueble = 'Apto ' . $venta->apartamento->numero;
                } elseif ($venta?->local) {
                    $inmueble = 'Local ' . $venta->local->numero;
                }

                return [
                    'id_pago' => $pago->id_pago,
                    'fecha' => $pago->fecha,
                    'valor' => $pago->valor,
                    'referencia_pago' => $pago->referencia_pago,
                    'descripcion' => $pago->descripcion,
                    'id_venta' => $pago->id_venta,
                    'id_cuota' => $pago->id_cuota,
                    'cliente' => $venta?->cliente?->nombre,
                    'documento_cliente' => $venta?->cliente?->documento,
                    'proyecto' => $venta?->proyecto?->nombre,
                    'inmueble' => $inmueble,
                    'concepto_pago' => $pago->conceptoPago?->concepto,
                    'medio_pago' => $pago->medioPago?->medio_pago,
                    'numero_cuota' => $pago->cuota?->numero_cuota,
                    'tiene_comprobante' => $pago->tiene_comprobante,
                    'comprobante_url' => $pago->comprobante_url,
                    'comprobante_nombre_original' => $pago->comprobante_nombre_original,
                ];
            });

        return Inertia::render('Ventas/Pagos/Index', [
            'pagos' => $pagos,
            'empleado' => $empleado,
        ]);
    }

    public function create(Request $request)
    {
        $empleado = $request->user()->load('cargo');

        $ventas = Venta::with(['cliente', 'proyecto', 'apartamento', 'local'])
            ->orderBy('fecha_venta', 'desc')
            ->get()
            ->map(function ($venta) {
                $inmueble = '—';
                if ($venta->apartamento) {
                    $inmueble = 'Apto ' . $venta->apartamento->numero;
                } elseif ($venta->local) {
                    $inmueble = 'Local ' . $venta->local->numero;
                }

                return [
                    'id_venta' => $venta->id_venta,
                    'cliente' => $venta->cliente?->nombre,
                    'documento_cliente' => $venta->cliente?->documento,
                    'proyecto' => $venta->proyecto?->nombre,
                    'inmueble' => $inmueble,
                    'fecha_venta' => $venta->fecha_venta,
                    'valor_total' => $venta->valor_total,
                    'label' => trim(($venta->cliente?->nombre ?? 'Sin cliente') . ' · ' . $inmueble . ' · ' . ($venta->proyecto?->nombre ?? 'Sin proyecto')),
                ];
            });

        $conceptos = ConceptoPago::orderBy('concepto')->get(['id_concepto_pago', 'concepto']);
        $medios = MedioPago::orderBy('medio_pago')->get(['id_medio_pago', 'medio_pago']);

        $cuotas = PlanAmortizacionCuota::with([
            'planAmortizacion.venta.cliente',
            'planAmortizacion.venta.proyecto',
            'planAmortizacion.venta.apartamento',
            'planAmortizacion.venta.local',
        ])
            ->orderBy('fecha_vencimiento')
            ->get()
            ->map(function ($cuota) {
                $venta = $cuota->planAmortizacion?->venta;

                $inmueble = '—';
                if ($venta?->apartamento) {
                    $inmueble = 'Apto ' . $venta->apartamento->numero;
                } elseif ($venta?->local) {
                    $inmueble = 'Local ' . $venta->local->numero;
                }

                return [
                    'id_cuota' => $cuota->id_cuota,
                    'id_venta' => $venta?->id_venta,
                    'numero_cuota' => $cuota->numero_cuota,
                    'fecha_vencimiento' => $cuota->fecha_vencimiento,
                    'valor_cuota' => $cuota->valor_cuota,
                    'estado' => $cuota->estado,
                    'cliente' => $venta?->cliente?->nombre,
                    'documento_cliente' => $venta?->cliente?->documento,
                    'proyecto' => $venta?->proyecto?->nombre,
                    'inmueble' => $inmueble,
                    'label' => 'Cuota #' . $cuota->numero_cuota . ' - ' . number_format((float) $cuota->valor_cuota, 0, ',', '.'),
                ];
            });

        return Inertia::render('Ventas/Pagos/Create', [
            'ventas' => $ventas,
            'conceptos' => $conceptos,
            'medios' => $medios,
            'cuotas' => $cuotas,
            'empleado' => $empleado,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha' => 'nullable|date',
            'id_venta' => 'required|exists:ventas,id_venta',
            'referencia_pago' => 'nullable|string|max:60',
            'id_concepto_pago' => 'nullable|exists:conceptos_pago,id_concepto_pago',
            'id_medio_pago' => 'nullable|exists:medios_pago,id_medio_pago',
            'descripcion' => 'nullable|string',
            'valor' => 'nullable|numeric|min:0',
            'id_cuota' => 'nullable|exists:planes_amortizacion_cuota,id_cuota',
            'comprobante' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:1024',
        ], [
            'comprobante.image' => 'El comprobante debe ser una imagen válida.',
            'comprobante.mimes' => 'El comprobante debe estar en formato jpg, jpeg, png o webp.',
            'comprobante.max' => 'El comprobante no debe superar 1 MB.',
        ]);

        $data = [
            'fecha' => $validated['fecha'] ?? now(),
            'id_venta' => $validated['id_venta'],
            'referencia_pago' => $validated['referencia_pago'] ?? null,
            'id_concepto_pago' => $validated['id_concepto_pago'] ?? null,
            'id_medio_pago' => $validated['id_medio_pago'] ?? null,
            'descripcion' => $validated['descripcion'] ?? null,
            'valor' => $validated['valor'] ?? null,
            'id_cuota' => $validated['id_cuota'] ?? null,
        ];

        if ($request->hasFile('comprobante')) {
            $archivo = $request->file('comprobante');
            $ruta = $archivo->store('pagos/comprobantes', 'public');

            $data['comprobante_path'] = $ruta;
            $data['comprobante_nombre_original'] = $archivo->getClientOriginalName();
            $data['comprobante_mime'] = $archivo->getClientMimeType();
            $data['comprobante_size'] = $archivo->getSize();
        }

        $pago = Pago::create($data);

        return redirect()
            ->route('pagos.show', $pago->id_pago)
            ->with('success', 'Pago registrado exitosamente.');
    }

    public function show(Request $request, $id)
    {
        $empleado = $request->user()->load('cargo');

        $pago = Pago::with([
            'venta.cliente',
            'venta.proyecto',
            'venta.apartamento',
            'venta.local',
            'conceptoPago',
            'medioPago',
            'cuota.planAmortizacion.venta',
        ])
            ->findOrFail($id);

        return Inertia::render('Ventas/Pagos/Show', [
            'pago' => $pago,
            'empleado' => $empleado,
        ]);
    }

    public function edit(Request $request, $id)
    {
        $empleado = $request->user()->load('cargo');

        $pago = Pago::findOrFail($id);

        $ventas = Venta::with(['cliente', 'proyecto', 'apartamento', 'local'])
            ->orderBy('fecha_venta', 'desc')
            ->get()
            ->map(function ($venta) {
                $inmueble = '—';
                if ($venta->apartamento) {
                    $inmueble = 'Apto ' . $venta->apartamento->numero;
                } elseif ($venta->local) {
                    $inmueble = 'Local ' . $venta->local->numero;
                }

                return [
                    'id_venta' => $venta->id_venta,
                    'cliente' => $venta->cliente?->nombre,
                    'documento_cliente' => $venta->cliente?->documento,
                    'proyecto' => $venta->proyecto?->nombre,
                    'inmueble' => $inmueble,
                    'fecha_venta' => $venta->fecha_venta,
                    'valor_total' => $venta->valor_total,
                    'label' => trim(($venta->cliente?->nombre ?? 'Sin cliente') . ' · ' . $inmueble . ' · ' . ($venta->proyecto?->nombre ?? 'Sin proyecto')),
                ];
            });

        $conceptos = ConceptoPago::orderBy('concepto')->get(['id_concepto_pago', 'concepto']);
        $medios = MedioPago::orderBy('medio_pago')->get(['id_medio_pago', 'medio_pago']);

        $cuotas = PlanAmortizacionCuota::with([
            'planAmortizacion.venta.cliente',
            'planAmortizacion.venta.proyecto',
            'planAmortizacion.venta.apartamento',
            'planAmortizacion.venta.local',
        ])
            ->orderBy('fecha_vencimiento')
            ->get()
            ->map(function ($cuota) {
                $venta = $cuota->planAmortizacion?->venta;

                $inmueble = '—';
                if ($venta?->apartamento) {
                    $inmueble = 'Apto ' . $venta->apartamento->numero;
                } elseif ($venta?->local) {
                    $inmueble = 'Local ' . $venta->local->numero;
                }

                return [
                    'id_cuota' => $cuota->id_cuota,
                    'id_venta' => $venta?->id_venta,
                    'numero_cuota' => $cuota->numero_cuota,
                    'fecha_vencimiento' => $cuota->fecha_vencimiento,
                    'valor_cuota' => $cuota->valor_cuota,
                    'estado' => $cuota->estado,
                    'cliente' => $venta?->cliente?->nombre,
                    'documento_cliente' => $venta?->cliente?->documento,
                    'proyecto' => $venta?->proyecto?->nombre,
                    'inmueble' => $inmueble,
                    'label' => 'Cuota #' . $cuota->numero_cuota . ' - ' . number_format((float) $cuota->valor_cuota, 0, ',', '.'),
                ];
            });

        return Inertia::render('Ventas/Pagos/Edit', [
            'pago' => $pago,
            'ventas' => $ventas,
            'conceptos' => $conceptos,
            'medios' => $medios,
            'cuotas' => $cuotas,
            'empleado' => $empleado,
        ]);
    }

    public function update(Request $request, $id)
    {
        $pago = Pago::findOrFail($id);

        $validated = $request->validate([
            'fecha' => 'nullable|date',
            'id_venta' => 'required|exists:ventas,id_venta',
            'referencia_pago' => 'nullable|string|max:60',
            'id_concepto_pago' => 'nullable|exists:conceptos_pago,id_concepto_pago',
            'id_medio_pago' => 'nullable|exists:medios_pago,id_medio_pago',
            'descripcion' => 'nullable|string',
            'valor' => 'nullable|numeric|min:0',
            'id_cuota' => 'nullable|exists:planes_amortizacion_cuota,id_cuota',
            'comprobante' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:1024',
            'eliminar_comprobante' => 'nullable|boolean',
        ], [
            'comprobante.image' => 'El comprobante debe ser una imagen válida.',
            'comprobante.mimes' => 'El comprobante debe estar en formato jpg, jpeg, png o webp.',
            'comprobante.max' => 'El comprobante no debe superar 1 MB.',
        ]);

        $data = [
            'fecha' => $validated['fecha'] ?? $pago->fecha,
            'id_venta' => $validated['id_venta'],
            'referencia_pago' => $validated['referencia_pago'] ?? null,
            'id_concepto_pago' => $validated['id_concepto_pago'] ?? null,
            'id_medio_pago' => $validated['id_medio_pago'] ?? null,
            'descripcion' => $validated['descripcion'] ?? null,
            'valor' => $validated['valor'] ?? null,
            'id_cuota' => $validated['id_cuota'] ?? null,
        ];

        if (($validated['eliminar_comprobante'] ?? false) && $pago->comprobante_path) {
            Storage::disk('public')->delete($pago->comprobante_path);

            $data['comprobante_path'] = null;
            $data['comprobante_nombre_original'] = null;
            $data['comprobante_mime'] = null;
            $data['comprobante_size'] = null;
        }

        if ($request->hasFile('comprobante')) {
            if ($pago->comprobante_path) {
                Storage::disk('public')->delete($pago->comprobante_path);
            }

            $archivo = $request->file('comprobante');
            $ruta = $archivo->store('pagos/comprobantes', 'public');

            $data['comprobante_path'] = $ruta;
            $data['comprobante_nombre_original'] = $archivo->getClientOriginalName();
            $data['comprobante_mime'] = $archivo->getClientMimeType();
            $data['comprobante_size'] = $archivo->getSize();
        }

        $pago->update($data);

        return redirect()
            ->route('pagos.show', $pago->id_pago)
            ->with('success', 'Pago actualizado correctamente.');
    }

    public function destroy($id)
    {
        $pago = Pago::findOrFail($id);

        if ($pago->comprobante_path) {
            Storage::disk('public')->delete($pago->comprobante_path);
        }

        $pago->delete();

        return redirect()
            ->route('pagos.index')
            ->with('success', 'Pago eliminado correctamente.');
    }
}
