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
use Illuminate\Support\Facades\DB;

class VentaWebController extends Controller
{
    /* ===========================================================
     *  INDEX
     * =========================================================== */

    public function index()
    {
        $proyecto = Proyecto::first(); // temporal para debug, escoger el proyecto real

        $ventasActivas = Venta::where('id_proyecto', $proyecto->id_proyecto)
            ->whereIn('tipo_operacion', ['venta', 'separacion'])
            ->count();

        // PriceEngine
        $pe = new \App\Services\PriceEngine();
        $peBloque = $pe->obtenerBloqueActual($proyecto);
        $peFactor = $pe->calcularFactorAumento($proyecto, $peBloque);
        $pePoliticas = $proyecto->politicasPrecio()->get()->toArray();

        // ProyectoPricingService
        $pps = new \App\Services\ProyectoPricingService();
        $bloque = $peBloque;
        $factor = $peFactor;
        $politicas = $proyecto->politicasPrecio()->get();

        $ventas = Venta::with([
            'cliente',
            'empleado',
            'apartamento.estadoInmueble',
            'local.estadoInmueble',
            'proyecto',
            'formaPago',
        ])->orderBy('fecha_venta', 'desc')->get();

        return Inertia::render('Ventas/Venta/Index', [
            'ventas' => $ventas,
            // Debug ProyectoPricingService
            'debug_proyecto' => [
                'nombre' => $proyecto->nombre ?? null,
                'ventas_activas' => $ventasActivas ?? null,
                'bloque_actual' => $bloque ?? null,
                'factor' => $factor ?? null,
                'politicas' => $politicas->map(function ($p) {
                    return [
                        'id' => $p->id_politica_precio,
                        'ventas_por_escalon' => $p->ventas_por_escalon,
                        'porcentaje_aumento' => $p->porcentaje_aumento,
                        'aplica_desde' => $p->aplica_desde,
                    ];
                }),
            ],

            // Debug PriceEngine
            'debug_priceengine' => [
                'bloque' => $peBloque ?? null,
                'factor' => $peFactor ?? null,
                'politicas' => $pePoliticas ?? null,
            ],

            // Debug VentaService (solo si hubo venta)
            'debug_venta' => session('debug_venta', null),
        ]);
    }

    /* ===========================================================
     *  CREATE
     * =========================================================== */

    public function create(Request $request)
    {
        $clientes = Cliente::orderBy('nombre')->get();
        $empleados = Empleado::orderBy('nombre')->get();

        // Solo disponibles
        $apartamentos = Apartamento::with(['torre.proyecto', 'estadoInmueble'])
            ->whereHas('estadoInmueble', fn($q) => $q->where('nombre', 'Disponible'))
            ->get();

        $locales = Local::with(['torre.proyecto', 'estadoInmueble'])
            ->whereHas('estadoInmueble', fn($q) => $q->where('nombre', 'Disponible'))
            ->get();

        $proyectos = Proyecto::orderBy('nombre')->get();
        $formasPago = FormaPago::orderBy('forma_pago')->get();
        $estadosInmueble = EstadoInmueble::orderBy('nombre')->get();

        $inmueblePrecargado = null;
        $tipo = null;

        if ($request->has('inmueble_tipo') && $request->has('inmueble_id')) {

            $tipo = $request->inmueble_tipo;

            if ($tipo === 'apartamento') {
                $inmueblePrecargado = Apartamento::with(['torre.proyecto', 'estadoInmueble'])
                    ->find($request->inmueble_id);
            } elseif ($tipo === 'local') {
                $inmueblePrecargado = Local::with(['torre.proyecto', 'estadoInmueble'])
                    ->find($request->inmueble_id);
            }

            // if ($inmueblePrecargado) {

            //     // Validar bloqueo previo
            //     $bloqueado = BloqueoInmueble::where('id_inmueble', $request->inmueble_id)
            //         ->where('inmueble_tipo', $tipo)
            //         ->whereNull('released_at')
            //         ->where('expires_at', '>', now())
            //         ->first();

            //     if ($bloqueado && $bloqueado->id_empleado !== auth()->user()->empleado) {
            //         return back()->withErrors([
            //             'inmueble' => 'El inmueble está siendo operado por otro asesor.'
            //         ]);
            //     }

            //     // Congelar
            //     $this->congelarInmueble($inmueblePrecargado, $tipo, auth()->user()->empleado);
            // }
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



    /* ===========================================================
     *  STORE
     * =========================================================== */

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {

            $validated = $request->validate([
                'tipo_operacion'    => 'required|in:venta,separacion',
                'id_empleado'       => 'required|exists:empleados,id_empleado',
                'documento_cliente' => 'required|exists:clientes,documento',
                'fecha_venta'       => 'required|date',
                'inmueble_tipo'     => 'required|in:apartamento,local',
                'inmueble_id'       => 'required|integer',
                'id_forma_pago'     => 'required',
                'id_estado_inmueble' => 'required',
                'id_proyecto'       => 'nullable',
                'cuota_inicial'     => 'nullable|numeric|min:0',
                'valor_separacion'  => 'nullable|numeric|min:0',
                'valor_total'       => 'nullable|numeric|min:0',
                'descripcion'       => 'nullable|max:300'
            ]);

            /* --------------------
             *  Obtener inmueble
             * -------------------- */
            $tipo = $validated['inmueble_tipo'];

            if ($tipo === 'apartamento') {
                $inmueble = Apartamento::with('torre.proyecto')->findOrFail($validated['inmueble_id']);
                $validated['id_apartamento'] = $inmueble->id_apartamento;
                $validated['id_local'] = null;
            } else {
                $inmueble = Local::with('torre.proyecto')->findOrFail($validated['inmueble_id']);
                $validated['id_local'] = $inmueble->id_local;
                $validated['id_apartamento'] = null;
            }

            /* --------------------
             *  Inferir proyecto
             * -------------------- */
            if (!$validated['id_proyecto']) {
                $validated['id_proyecto'] = $inmueble->torre->id_proyecto;
            }

            $proyecto = Proyecto::find($validated['id_proyecto']);

            /* =======================================================
             *   VALIDACIONES DE NEGOCIO
             * ======================================================= */

            if ($validated['tipo_operacion'] === Venta::TIPO_VENTA) {

                $porcMin = (float) ($proyecto->porcentaje_cuota_inicial_min ?? 0);
                $valorTotal = $validated['valor_total'] ?? $inmueble->valor_final;

                if ($porcMin > 0) {
                    $minimo = $valorTotal * ($porcMin / 100);
                    if ($validated['cuota_inicial'] < $minimo) {
                        return back()->withErrors([
                            'cuota_inicial' => 'La cuota inicial mínima es ' .
                                number_format($minimo, 0, ',', '.') . ' (' . $porcMin . '%).'
                        ]);
                    }
                }

                $validated['valor_separacion'] = null;
            }

            if ($validated['tipo_operacion'] === Venta::TIPO_SEPARACION) {

                $minSep = $proyecto->valor_min_separacion ?? 0;
                if ($validated['valor_separacion'] < $minSep) {
                    return back()->withErrors([
                        'valor_separacion' =>
                        'El valor mínimo de separación es ' . number_format($minSep, 0, ',', '.')
                    ]);
                }

                $validated['cuota_inicial'] = null;
            }

            unset($validated['inmueble_tipo'], $validated['inmueble_id']);

            // Crear venta
            $venta = Venta::create($validated);

            // Congelar inmueble
            $inmueble->update([
                'id_estado_inmueble' => $validated['id_estado_inmueble'],
            ]);

            // Recalcular precios del proyecto (bloque/escalón)
            app(\App\Services\PriceEngine::class)
                ->recalcularProyectoPorVenta($venta);

            return redirect()
                ->route('ventas.index')
                ->with('success', 'Operación registrada exitosamente.');
        });
    }

    /* ===========================================================
     *  SHOW
     * =========================================================== */

    public function show($id)
    {
        $venta = Venta::with([
            'cliente',
            'empleado',
            'apartamento.estadoInmueble',
            'local.estadoInmueble',
            'proyecto',
            'formaPago',
            'planAmortizacion.cuotas'
        ])->findOrFail($id);

        return Inertia::render('Ventas/Venta/Show', [
            'venta' => $venta,
        ]);
    }

    /* ===========================================================
     *  EDIT / UPDATE
     * =========================================================== */

    public function edit($id)
    {
        $venta = Venta::with(['apartamento', 'local', 'proyecto'])
            ->findOrFail($id);

        return Inertia::render('Ventas/Venta/Edit', [
            'venta' => $venta,
            'clientes' => Cliente::all(),
            'empleados' => Empleado::all(),
            'apartamentos' => Apartamento::with(['torre.proyecto'])->get(),
            'locales' => Local::with(['torre.proyecto'])->get(),
            'proyectos' => Proyecto::all(),
            'formasPago' => FormaPago::all(),
            'estadosInmueble' => EstadoInmueble::all(),
        ]);
    }

    public function update(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {

            $venta = Venta::findOrFail($id);

            $validated = $request->validate([
                'tipo_operacion'    => 'required',
                'id_empleado'       => 'required',
                'documento_cliente' => 'required',
                'fecha_venta'       => 'required',
                'inmueble_tipo'     => 'required',
                'inmueble_id'       => 'required'
            ]);

            /* --------------------
             *  Inmueble nuevo
             * -------------------- */
            $tipo = $validated['inmueble_tipo'];

            if ($tipo === 'apartamento') {
                $nuevo = Apartamento::with('torre.proyecto')->findOrFail($validated['inmueble_id']);
                $validated['id_apartamento'] = $nuevo->id_apartamento;
                $validated['id_local'] = null;
            } else {
                $nuevo = Local::with('torre.proyecto')->findOrFail($validated['inmueble_id']);
                $validated['id_local'] = $nuevo->id_local;
                $validated['id_apartamento'] = null;
            }

            unset($validated['inmueble_tipo'], $validated['inmueble_id']);

            $venta->update($validated);

            // Actualizar estado del INMUEBLE NUEVO
            $nuevo->update([
                'id_estado_inmueble' => $validated['id_estado_inmueble']
            ]);

            // Recalcular precios (bloques)
            app(\App\Services\PriceEngine::class)
                ->recalcularProyectoPorVenta($venta);

            return redirect()
                ->route('ventas.show', $venta->id_venta)
                ->with('success', 'Operación actualizada correctamente.');
        });
    }

    /* ===========================================================
     *  DESTROY
     * =========================================================== */

    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {

            $venta = Venta::findOrFail($id);

            // Identificar proyecto asociado ANTES de borrar la venta
            $proyectoAsociado = Proyecto::find($venta->id_proyecto);

            // Liberar inmueble
            if ($venta->id_apartamento) {
                $inmueble = Apartamento::find($venta->id_apartamento);
            } elseif ($venta->id_local) {
                $inmueble = Local::find($venta->id_local);
            } else {
                $inmueble = null;
            }

            if ($inmueble) {
                $inmueble->update(['id_estado_inmueble' => 1]);
            }

            $venta->delete();

            // Recalcular precios tras reversión del bloque
            if ($proyectoAsociado) {
                app(\App\Services\PriceEngine::class)
                    ->recalcularProyecto($proyectoAsociado);
            }

            return redirect()
                ->route('ventas.index')
                ->with('success', 'Operación eliminada correctamente.');
        });
    }

    public function cancelarSeparacion($id)
    {
        $venta = Venta::with(['apartamento', 'local'])->findOrFail($id);

        if (!$venta->esSeparacion()) {
            return back()->withErrors(['operacion' => 'Esta operación no es una separación.']);
        }

        $inmueble = $venta->id_apartamento ? $venta->apartamento : $venta->local;
        $proyecto = Proyecto::find($venta->id_proyecto);

        // Liberar
        $estadoDisponible = EstadoInmueble::where('nombre', 'Disponible')->first();

        $inmueble->update([
            'id_estado_inmueble' => $estadoDisponible->id_estado_inmueble
        ]);

        // Eliminar separación
        $venta->delete();

        // Recalcular bloque/precios
        if ($proyecto) {
            app(\App\Services\PriceEngine::class)->recalcularProyecto($proyecto);
        }

        return back()->with('success', 'Separación cancelada correctamente.');
    }

    public function convertirEnVenta($id)
    {
        $venta = Venta::with(['apartamento', 'local'])->findOrFail($id);

        if (!$venta->esSeparacion()) {
            return back()->withErrors(['operacion' => 'Esta operación no es una separación.']);
        }

        $proyecto = Proyecto::find($venta->id_proyecto);
        $inmueble = $venta->id_apartamento ? $venta->apartamento : $venta->local;

        $estadoVendido = EstadoInmueble::where('nombre', 'Vendido')->first();

        $inmueble->update([
            'id_estado_inmueble' => $estadoVendido->id_estado_inmueble
        ]);

        // Convertir tipo de operación
        $venta->update([
            'tipo_operacion' => 'venta',
            'cuota_inicial' => $venta->valor_separacion,
            'valor_separacion' => null,
        ]);

        // Recalcular precios del proyecto
        if ($proyecto) {
            app(\App\Services\PriceEngine::class)
                ->recalcularProyecto($proyecto);
        }

        return redirect()
            ->route('ventas.show', $venta->id_venta)
            ->with('success', 'La separación ahora es una venta.');
    }
}
