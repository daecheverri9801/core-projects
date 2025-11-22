<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\Proyecto;
use App\Models\Cliente;
use App\Models\Apartamento;
use App\Models\Local;
use Inertia\Inertia;

class CotizadorWebController extends Controller
{
    public function index()
    {
        $apartamentos = Apartamento::with([
            'torre.proyecto',
            'tipoApartamento',
            'pisoTorre',
            'estadoInmueble'
        ])
            ->whereHas('estadoInmueble', fn($q) => $q->where('nombre', 'Disponible'))
            ->get()
            ->map(fn($a) => [
                'tipo' => 'apartamento',
                'id' => $a->id_apartamento,
                'numero' => $a->numero,
                'valor_final' => $a->valor_final,
                'torre' => $a->torre,
                'pisoTorre' => $a->pisoTorre,
                'tipoApartamento' => $a->tipoApartamento
            ]);

        // $locales = Local::with([
        //     'torre.proyecto',
        //     'estadoInmueble'
        // ])
        //     ->whereHas('estadoInmueble', fn($q) => $q->where('nombre', 'Disponible'))
        //     ->get()
        //     ->map(fn($l) => [
        //         'tipo' => 'local',
        //         'id' => $l->id_local,
        //         'numero' => $l->numero,
        //         'valor_final' => $l->valor_final,
        //         'torre' => $l->torre,
        //         'pisoTorre' => $l->pisoTorre
        //     ]);
        return Inertia::render('Ventas/Cotizador/Index', [
            'proyectos' => Proyecto::select('id_proyecto', 'nombre', 'plazo_cuota_inicial_meses', 'porcentaje_cuota_inicial_min', 'valor_min_separacion')->get(),
            'clientes'  => Cliente::select('documento', 'nombre', 'direccion', 'telefono', 'correo')->get(),
            'inmuebles' => $apartamentos->values(),
        ]);
    }
}
