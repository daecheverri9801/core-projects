<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Apartamento;
use App\Models\Local;
use App\Models\Proyecto;
use App\Models\FormaPago;
use App\Models\EstadoInmueble;
use App\Services\VentaService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class VentaAdminController extends Controller
{

    public function __construct(
        protected VentaService $ventaService
    ) {}
    
    /* ===========================================================
     *  INDEX
     * =========================================================== */

    public function index(Request $request)
    {
        $empleado = $request->user()->load('cargo');
        $proyecto = Proyecto::first();

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

        return Inertia::render('Admin/Ventas/Index', [
            'empleado' => $empleado,
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
        $empleado = $request->user()->load('cargo');
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


        $plazos = [];

        if ($inmueblePrecargado) {
            $proyecto = $inmueblePrecargado->torre->proyecto;
            $plazos = $this->calcularPlazosDisponibles($proyecto);
        }

        return Inertia::render('Admin/Ventas/Create', [
            'empleado' => $empleado,
            'clientes' => $clientes,
            'empleados' => $empleados,
            'apartamentos' => $apartamentos,
            'locales' => $locales,
            'proyectos' => $proyectos,
            'formasPago' => $formasPago,
            'estadosInmueble' => $estadosInmueble,
            'inmueblePrecargado' => $inmueblePrecargado,
            'plazos_disponibles' => $plazos,

            // DEBUG directo para Vue
            'debug_plazos' => [
                'proyectos' => $proyectos->map(fn($p) => [
                    'id' => $p->id_proyecto,
                    'nombre' => $p->nombre,
                    'fecha_inicio' => $p->fecha_inicio,
                    'plazo' => $p->plazo_cuota_inicial_meses,
                ]),
            ],
        ]);
    }

    private function calcularPlazosDisponibles(Proyecto $proyecto)
    {

        if (!$proyecto->fecha_inicio || !$proyecto->plazo_cuota_inicial_meses) {
            return [];
        }

        $inicio = \Carbon\Carbon::parse($proyecto->fecha_inicio);
        $mesesTranscurridos = $inicio->diffInMonths(now());

        $max = $proyecto->plazo_cuota_inicial_meses;
        $restantes = max($max - $mesesTranscurridos, 0);

        return range(1, $restantes);
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

                'id_proyecto'       => 'required|exists:proyectos,id_proyecto',

                'inmueble_tipo'     => 'required|in:apartamento,local',
                'inmueble_id'       => 'required|integer',

                'id_forma_pago'     => 'required|exists:formas_pago,id_forma_pago',

                // Nota: el service decide el estado final del inmueble (Vendido/Separado).
                // Puedes dejarlo opcional si no quieres enviarlo desde el front.
                'id_estado_inmueble' => 'nullable|exists:estados_inmueble,id_estado_inmueble',

                'cuota_inicial'     => 'nullable|numeric|min:0',
                'valor_separacion'  => 'nullable|numeric|min:0',
                'fecha_limite_separacion' => 'nullable|date|after_or_equal:today',

                'valor_total'       => 'nullable|numeric|min:0',
                'valor_restante'    => 'nullable|numeric|min:0',
                'descripcion'       => 'nullable|max:300',

                'plazo_cuota_inicial_meses' => 'nullable|integer|min:0',
                'frecuencia_cuota_inicial_meses' => 'nullable|integer|min:1',
            ]);

            // Defaults coherentes
            if (($validated['tipo_operacion'] ?? null) === 'venta') {
                $validated['frecuencia_cuota_inicial_meses'] = (int)($validated['frecuencia_cuota_inicial_meses'] ?? 1);
            } else {
                $validated['frecuencia_cuota_inicial_meses'] = null;
                $validated['plazo_cuota_inicial_meses'] = null;
                $validated['cuota_inicial'] = null;
            }

            // Si no quieres manejar estados desde el front:
            // $validated['id_estado_inmueble'] = null;

            // Delegar al servicio (genera plan, cambia estado inmueble, recalcula precios)
            $venta = $this->ventaService->crearOperacion($validated);

            return redirect()
                ->route('admin.ventas.index', $venta->id_venta)
                ->with('success', 'Operación registrada exitosamente.');
        });
    }

    /* ===========================================================
     *  EDIT / UPDATE
     * =========================================================== */

    public function edit(Request $request, $id)
    {
        $empleado = $request->user()->load('cargo');
        $venta = Venta::with(['apartamento', 'local', 'proyecto'])
            ->findOrFail($id);

        $proyecto = $venta->proyecto;
        $plazos = $this->calcularPlazosDisponibles($proyecto);

        return Inertia::render('Admin/Ventas/Edit', [
            'venta' => $venta,
            'clientes' => Cliente::all(),
            'empleados' => Empleado::all(),
            'apartamentos' => Apartamento::with(['torre.proyecto'])->get(),
            'locales' => Local::with(['torre.proyecto'])->get(),
            'proyectos' => Proyecto::all(),
            'formasPago' => FormaPago::all(),
            'estadosInmueble' => EstadoInmueble::all(),
            'plazos_disponibles' => $plazos,
            'empleado' => $empleado,
        ]);
    }

    public function update(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {

            $venta = Venta::findOrFail($id);

            /* --------------------
         *  VALIDACIÓN COMPLETA
         * -------------------- */
            $validated = $request->validate([
                'tipo_operacion'    => 'required|in:venta,separacion',
                'id_empleado'       => 'required|exists:empleados,id_empleado',
                'documento_cliente' => 'required|exists:clientes,documento',
                'fecha_venta'       => 'required|date',
                'id_proyecto'       => 'required|exists:proyectos,id_proyecto',
                'inmueble_tipo'     => 'required|in:apartamento,local',
                'inmueble_id'       => 'required|integer',
                'id_forma_pago'     => 'required|exists:formas_pago,id_forma_pago',
                'id_estado_inmueble' => 'required|exists:estados_inmueble,id_estado_inmueble',
                'valor_total'       => 'nullable|numeric|min:0',
                'cuota_inicial'     => 'nullable|numeric|min:0',
                'valor_restante'    => 'nullable|numeric|min:0',
                'descripcion'       => 'nullable|string|max:300',
                'valor_separacion'  => 'nullable|numeric|min:0',
                'fecha_limite_separacion' => 'nullable|date|after_or_equal:today',
                'plazo_cuota_inicial_meses' => 'nullable|integer|min:0', // ✅ CAMBIAR a nullable
                'frecuencia_cuota_inicial_meses' => 'nullable|integer|min:1',
            ]);

            // ✅ DEBUG: Verificar qué datos llegan
            \Log::info('Actualizando venta ID: ' . $id, [
                'plazo_cuota_inicial_meses' => $request->plazo_cuota_inicial_meses,
                'todos_los_datos' => $request->all()
            ]);

            /* --------------------
         *  RESOLVER INMUEBLE NUEVO
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

            /* --------------------
         *  DEFINIR ESTADO SEGÚN TIPO DE OPERACIÓN
         * -------------------- */
            $estadoDestino = $validated['tipo_operacion'] === 'venta'
                ? 'Vendido'
                : 'Separado';

            $idEstadoDestino = EstadoInmueble::where('nombre', $estadoDestino)
                ->value('id_estado_inmueble');

            // ✅ Asegurar que el estado del inmueble sea el correcto
            $validated['id_estado_inmueble'] = $idEstadoDestino;

            /* --------------------
         *  ACTUALIZAR VENTA
         * -------------------- */
            // ✅ El plazo ya viene en $validated por la validación
            $venta->update($validated);

            /* --------------------
         *  ACTUALIZAR EL INMUEBLE
         * -------------------- */
            $nuevo->update([
                'id_estado_inmueble' => $idEstadoDestino
            ]);

            /* --------------------
         *  RECALCULAR PRECIOS DE BLOQUES
         * -------------------- */
            app(\App\Services\PriceEngine::class)
                ->recalcularProyectoPorVenta($venta);

            return redirect()
                ->route('admin.ventas.index')
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
                ->route('admin.ventas.index')
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
            ->route('admin.ventas.index')
            ->with('success', 'La separación ahora es una venta.');
    }

    public function getPlazosDisponibles(Proyecto $proyecto)
    {
        if (!$proyecto->fecha_inicio || !$proyecto->plazo_cuota_inicial_meses) {
            return [];
        }

        $inicio = \Carbon\Carbon::parse($proyecto->fecha_inicio);
        $mesesTranscurridos = $inicio->diffInMonths(now());

        $maxPlazo = $proyecto->plazo_cuota_inicial_meses;
        $plazoRestante = max($maxPlazo - $mesesTranscurridos, 0);

        return range(1, $plazoRestante);
    }
}
