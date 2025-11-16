<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\Apartamento;
use App\Models\Local;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SimuladorWebController extends Controller
{
    public function index($tipo, $id)
    {
        if ($tipo === 'apartamento') {
            $item = Apartamento::with(['torre.proyecto'])
                ->findOrFail($id);

            $valorFinal = $item->valor_final ?? $item->valor_total;
            $porcentaje = $item->torre->proyecto->porcentaje_cuota_inicial_min ?? 0;

            $cuotaInicial = $valorFinal * ($porcentaje / 100);
        } else {
            $item = Local::with(['torre.proyecto'])
                ->findOrFail($id);

            $valorFinal = $item->valor_total;
            $porcentaje = $item->torre->proyecto->porcentaje_cuota_inicial_min ?? 0;

            $cuotaInicial = $valorFinal * ($porcentaje / 100);
        }

        return Inertia::render('Ventas/Simulador/Index', [
            'tipo'          => $tipo,
            'inmueble'      => $item,
            'valor_final'   => $valorFinal,
            'porcentaje'    => $porcentaje,
            'cuota_inicial' => $cuotaInicial,
        ]);
    }
}
