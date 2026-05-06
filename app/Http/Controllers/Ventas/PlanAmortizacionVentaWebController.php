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
        $ventas = Venta::with(['apartamento', 'local', 'formaPago', 'proyecto'])
            ->where('id_proyecto', $request->id_proyecto)
            ->where('documento_cliente', $request->documento_cliente)
            ->where('tipo_operacion', 'venta')
            ->get()
            ->map(function ($v) {
                $valorTotal = (float) ($v->valor_total ?? 0);
                $cuotaInicial = (float) ($v->cuota_inicial ?? 0);
                $valorSeparacion = (float) ($v->proyecto->valor_min_separacion ?? 0);

                $saldoCuotaInicial = max($cuotaInicial - $valorSeparacion, 0);
                $valorRestante = max($valorTotal - $cuotaInicial, 0);

                return [
                    'id_venta' => $v->id_venta,
                    'proyecto' => $v->proyecto->nombre ?? '',
                    'cliente' => $v->cliente->nombre ?? '',
                    'empleado' => trim(($v->empleado->nombre ?? '') . ' ' . ($v->empleado->apellido ?? '')),
                    'inmueble' => $v->apartamento
                        ? ('Apto ' . $v->apartamento->numero)
                        : ('Local ' . ($v->local->numero ?? '')),
                    'valor_total' => $valorTotal,
                    'cuota_inicial' => $cuotaInicial,
                    'valor_separacion' => $valorSeparacion,
                    'saldo_cuota_inicial' => $saldoCuotaInicial,
                    'valor_restante' => $valorRestante,
                    'plazo' => (int) ($v->plazo_cuota_inicial_meses ?? 0),
                    'fecha_venta' => $v->fecha_venta,
                    'forma_pago' => $v->formaPago->forma_pago ?? '',
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
