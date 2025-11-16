<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Proyecto;
use App\Models\Apartamento;
use App\Models\Local;
use App\Models\FormaPago;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CotizadorWebController extends Controller
{
    public function index()
    {
        return Inertia::render('Ventas/Cotizador/Index', [
            // Si luego agregamos BD de cotizaciones, aquí se cargan
            'cotizaciones' => [],
        ]);
    }

    public function create()
    {
        $clientes = Cliente::orderBy('nombre')->get();
        $proyectos = Proyecto::orderBy('nombre')->get();
        $formasPago = FormaPago::orderBy('forma_pago')->get();

        $apartamentos = Apartamento::with(['torre.proyecto', 'tipoApartamento'])
            ->where('id_estado_inmueble', 1) // Solo disponibles
            ->get();

        $locales = Local::with(['torre.proyecto'])
            ->where('id_estado_inmueble', 1)
            ->get();

        return Inertia::render('Ventas/Cotizador/Create', [
            'clientes'      => $clientes,
            'proyectos'     => $proyectos,
            'apartamentos'  => $apartamentos,
            'locales'       => $locales,
            'formasPago'    => $formasPago,
        ]);
    }

    public function store(Request $request)
    {
        // Por ahora solo devolvemos los datos calculados (no guardamos)
        $validated = $request->validate([
            'documento_cliente' => 'required|exists:clientes,documento',
            'id_proyecto'       => 'required|exists:proyectos,id_proyecto',
            'inmueble_tipo'     => 'required|in:apartamento,local',
            'inmueble_id'       => 'required|integer',
            'id_forma_pago'     => 'required|exists:formas_pago,id_forma_pago',
            'cuota_inicial'     => 'nullable|numeric|min:0',
            'total'             => 'required|numeric|min:0',
        ]);

        return redirect()
            ->route('cotizador.show')
            ->with('success', 'Cotización generada con éxito.');
    }

    public function show()
    {
        return Inertia::render('Ventas/Cotizador/Show', [
            // En esta versión inicial solo se mostrará la cotización calculada por el front
        ]);
    }
}
