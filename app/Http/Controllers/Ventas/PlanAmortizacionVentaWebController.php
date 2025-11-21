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
    public function index()
    {
        return Inertia::render('Ventas/Amortizacion/Index', [
            'proyectos' => Proyecto::select('id_proyecto', 'nombre')->orderBy('nombre')->get(),
            'clientes' => Cliente::select('documento', 'nombre')->orderBy('nombre')->get(),
        ]);
    }

    public function ventasPorCliente(Request $request)
    {
        $ventas = Venta::with(['apartamento', 'local', 'formaPago'])
            ->where('id_proyecto', $request->id_proyecto)
            ->where('documento_cliente', $request->documento_cliente)
            ->where('tipo_operacion', 'venta')
            ->get()
            ->map(function ($v) {
                return [
                    'id_venta' => $v->id_venta,
                    'proyecto' => $v->proyecto->nombre ?? '',
                    'cliente' => $v->cliente->nombre ?? '',
                    'empleado' => $v->empleado->nombre . ' ' . $v->empleado->apellido,
                    'inmueble' =>
                    $v->apartamento ? ('Apto ' . $v->apartamento->numero)
                        : ('Local ' . $v->local->numero),
                    'valor_total' => $v->valor_total,
                    'cuota_inicial' => $v->cuota_inicial,
                    'plazo' => $v->plazo_cuota_inicial_meses,
                    'fecha_venta' => $v->fecha_venta,
                    'forma_pago' => $v->formaPago->forma_pago ?? '',
                ];
            });

        return response()->json($ventas);
    }
}
