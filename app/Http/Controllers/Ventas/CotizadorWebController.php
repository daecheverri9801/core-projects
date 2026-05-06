<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\Proyecto;
use App\Models\Cliente;
use App\Models\Apartamento;
use App\Models\Empleado;
use App\Models\Local;
use Inertia\Inertia;
use Illuminate\Http\Request;

class CotizadorWebController extends Controller
{
    public function index(Request $request)
    {
        $empleado = $request->user()->load('cargo');

        $apartamentos = Apartamento::with([
            'torre.proyecto',
            'tipoApartamento',
            'pisoTorre',
            'estadoInmueble',
        ])
            ->whereHas('estadoInmueble', function ($q) {
                $q->whereRaw('LOWER(nombre) = ?', ['disponible']);
            })
            ->whereHas('torre.proyecto', function ($q) {
                $q->activos();
            })
            ->orderBy('numero', 'asc')
            ->get()
            ->map(function ($a) {
                return [
                    'tipo'            => 'apartamento',
                    'id'              => $a->id_apartamento,
                    'id_proyecto'     => $a->torre?->id_proyecto,
                    'numero'          => $a->numero,
                    'valor_final'     => $a->valor_final,
                    'torre'           => [
                        'id_torre'     => $a->torre?->id_torre,
                        'nombre_torre' => $a->torre?->nombre_torre,
                    ],
                    'pisoTorre'       => $a->pisoTorre,
                    'tipoApartamento' => $a->tipoApartamento,
                    'estadoInmueble'  => $a->estadoInmueble,
                ];
            });

        $locales = Local::with([
            'torre.proyecto',
            'estadoInmueble',
        ])
            ->whereHas('estadoInmueble', function ($q) {
                $q->whereRaw('LOWER(nombre) = ?', ['disponible']);
            })
            ->whereHas('torre.proyecto', function ($q) {
                $q->activos();
            })
            ->orderBy('numero', 'asc')
            ->get()
            ->map(function ($l) {
                return [
                    'tipo'            => 'local',
                    'id'              => $l->id_local,
                    'id_proyecto'     => $l->torre?->id_proyecto,
                    'numero'          => $l->numero,
                    'valor_final'     => $l->valor_total,
                    'torre'           => [
                        'id_torre'     => $l->torre?->id_torre,
                        'nombre_torre' => $l->torre?->nombre_torre,
                    ],
                    'pisoTorre'       => null,
                    'tipoApartamento' => null,
                    'estadoInmueble'  => $l->estadoInmueble,
                ];
            });

        $inmuebles = $apartamentos->values()->merge($locales->values());

        return Inertia::render('Ventas/Cotizador/Index', [
            'proyectos' => Proyecto::activos()
                ->with([
                    'planesPago' => function ($query) {
                        $query->activos()
                            ->orderBy('orden')
                            ->orderBy('id_plan_pago_proyecto');
                    },
                ])
                ->select(
                    'id_proyecto',
                    'nombre',
                    'logo_path',
                    'fecha_inicio',
                    'plazo_cuota_inicial_meses',
                    'porcentaje_cuota_inicial_min',
                    'valor_min_separacion'
                )
                ->orderBy('nombre', 'asc')
                ->get(),

            'clientes' => Cliente::select(
                'documento',
                'nombre',
                'direccion',
                'telefono',
                'correo'
            )
                ->orderBy('nombre', 'asc')
                ->get(),

            'empleado'  => $empleado,
            'inmuebles' => $inmuebles,
        ]);
    }
}
