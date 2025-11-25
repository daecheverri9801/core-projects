<?php

namespace App\Http\Controllers\Gerencia;

use App\Http\Controllers\Controller;
use App\Services\GerenciaEstadisticasService;
use Inertia\Inertia;

class GerenciaDashboardWebController extends Controller
{
    public function index(GerenciaEstadisticasService $service)
    {
        $resumenGlobal       = $service->resumenGlobal();
        $ventasPorProyecto   = $service->ventasPorProyecto();
        $proyeccionVsReal    = $service->proyeccionVsRealMensual();
        $velocidadVentas     = $service->velocidadVentasPorProyecto();
        $separacionesEfectiv = $service->separacionesYEfectividad();

        return Inertia::render('Gerencia/Dashboard/Index', [
            'resumenGlobal'        => $resumenGlobal,
            'ventasPorProyecto'    => $ventasPorProyecto,
            'proyeccionVsReal'     => $proyeccionVsReal,
            'velocidadVentas'      => $velocidadVentas,
            'separacionesEfectiv'  => $separacionesEfectiv,
        ]);
    }
}
