<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\Proyecto;
use App\Models\Cliente;
use App\Models\Venta;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanAmortizacionVentaWebController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Ventas/Amortizacion/Index', [
            'proyectos' => Proyecto::activos()->select('id_proyecto', 'nombre')->orderBy('nombre')->get(),
            'clientes' => Cliente::select('documento', 'nombre')->orderBy('nombre')->get(),
            'empleado' => $request->user()->load('cargo'),
        ]);
    }

    public function ventasPorCliente(Request $request)
    {
        $ventas = Venta::with([
            'apartamento',
            'local',
            'formaPago',
            'proyecto',
            'cliente',
            'empleado',
            'planAmortizacion.cuotas' => function ($query) {
                $query->orderBy('numero_cuota');
            },
        ])
            ->where('id_proyecto', $request->id_proyecto)
            ->where('documento_cliente', $request->documento_cliente)
            ->where('tipo_operacion', 'venta')
            ->get()
            ->map(function ($v) {
                $valorTotal = (float) ($v->valor_total ?? 0);
                $cuotaInicial = (float) ($v->cuota_inicial ?? 0);
                $valorSeparacion = (float) ($v->valor_separacion ?? 0);
                $saldoCuotaInicial = (float) ($v->saldo_cuota_inicial ?? max($cuotaInicial - $valorSeparacion, 0));
                $valorRestante = (float) ($v->valor_restante ?? max($valorTotal - $cuotaInicial, 0));

                return [
                    'id_venta' => $v->id_venta,
                    'proyecto' => $v->proyecto->nombre ?? '',
                    'cliente' => $v->cliente->nombre ?? '',
                    'documento_cliente' => $v->documento_cliente,
                    'empleado' => trim(($v->empleado->nombre ?? '') . ' ' . ($v->empleado->apellido ?? '')),
                    'inmueble' => $v->apartamento
                        ? ('Apto ' . $v->apartamento->numero)
                        : ('Local ' . ($v->local->numero ?? '')),
                    'valor_total' => $valorTotal,
                    'valor_total_sin_descuento' => (float) ($v->valor_total_sin_descuento ?? $valorTotal),
                    'valor_descuento' => (float) ($v->valor_descuento ?? 0),
                    'cuota_inicial' => $cuotaInicial,
                    'valor_separacion' => $valorSeparacion,
                    'saldo_cuota_inicial' => $saldoCuotaInicial,
                    'valor_restante' => $valorRestante,
                    'plazo' => (int) ($v->plazo_cuota_inicial_meses ?? 0),
                    'frecuencia_cuota_inicial_meses' => (int) ($v->frecuencia_cuota_inicial_meses ?? 1),
                    'fecha_venta' => $v->fecha_venta,
                    'forma_pago' => $v->formaPago->forma_pago ?? '',
                    'plan_pago_nombre' => $v->plan_pago_nombre,
                    'plan_pago_tipo' => $v->plan_pago_tipo,
                    'cuotas' => $v->planAmortizacion?->cuotas?->map(function ($c) {
                        return [
                            'numero' => $c->numero_cuota,
                            'fecha' => optional($c->fecha_vencimiento)->format('Y-m-d'),
                            'concepto' => $c->concepto ?? 'Cuota inicial',
                            'valor_cuota' => (float) ($c->valor_cuota ?? 0),
                            'saldo_final' => (float) ($c->saldo ?? 0),
                        ];
                    })->values()->all() ?? [],
                ];
            });

        return response()->json($ventas);
    }

    public function clientesPorProyecto(Request $request)
    {
        $clientes = Venta::where('id_proyecto', $request->id_proyecto)
            ->where('tipo_operacion', 'venta')
            ->with('cliente')
            ->get()
            ->pluck('cliente')
            ->unique('documento')
            ->values()
            ->map(function ($cliente) {
                return [
                    'documento' => $cliente->documento,
                    'nombre'    => $cliente->nombre,
                ];
            });

        return response()->json($clientes);
    }
}
