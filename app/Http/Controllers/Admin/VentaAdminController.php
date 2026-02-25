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
use App\Models\Parqueadero;
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

        $ventasActivas = $proyecto
            ? Venta::where('id_proyecto', $proyecto->id_proyecto)
            ->whereIn('tipo_operacion', ['venta', 'separacion'])
            ->count()
            : 0;

        // PriceEngine
        $pe = new \App\Services\PriceEngine();
        $peBloque = $proyecto ? $pe->obtenerBloqueActual($proyecto) : null;
        $peFactor = $proyecto ? $pe->calcularFactorAumento($proyecto, $peBloque) : null;
        $pePoliticas = $proyecto ? $proyecto->politicasPrecio()->get()->toArray() : [];

        // ProyectoPricingService (debug)
        $politicas = $proyecto ? $proyecto->politicasPrecio()->get() : collect();

        $ventas = Venta::with([
            'cliente',
            'empleado',
            'apartamento.estadoInmueble',
            'local.estadoInmueble',
            'proyecto',
            'formaPago',
            'parqueadero', // ✅ nuevo
        ])->orderBy('fecha_venta', 'desc')->get();

        return Inertia::render('Admin/Ventas/Index', [
            'empleado' => $empleado,
            'ventas' => $ventas,

            'debug_proyecto' => [
                'nombre' => $proyecto->nombre ?? null,
                'ventas_activas' => $ventasActivas ?? null,
                'bloque_actual' => $peBloque ?? null,
                'factor' => $peFactor ?? null,
                'politicas' => $politicas->map(function ($p) {
                    return [
                        'id' => $p->id_politica_precio,
                        'ventas_por_escalon' => $p->ventas_por_escalon,
                        'porcentaje_aumento' => $p->porcentaje_aumento,
                        'aplica_desde' => $p->aplica_desde,
                    ];
                }),
            ],

            'debug_priceengine' => [
                'bloque' => $peBloque ?? null,
                'factor' => $peFactor ?? null,
                'politicas' => $pePoliticas ?? null,
            ],

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

        $apartamentos = Apartamento::with(['torre.proyecto', 'estadoInmueble'])
            ->whereHas('estadoInmueble', fn($q) => $q->where('nombre', 'Disponible'))
            ->get();

        $locales = Local::with(['torre.proyecto', 'estadoInmueble'])
            ->whereHas('estadoInmueble', fn($q) => $q->where('nombre', 'Disponible'))
            ->get();

        $proyectos = Proyecto::orderBy('nombre')->get();
        $formasPago = FormaPago::orderBy('forma_pago')->get();
        $estadosInmueble = EstadoInmueble::orderBy('nombre')->get();

        // ✅ Parqueaderos adicionales disponibles:
        // - adicionales: id_apartamento NULL
        // - y no reservados por otra operación (ventas.id_parqueadero)
        $parqueaderos = Parqueadero::query()
            ->select('id_parqueadero', 'numero', 'tipo', 'id_proyecto', 'id_torre', 'precio')
            ->whereNull('id_apartamento')
            ->whereNotIn('id_parqueadero', function ($q) {
                $q->select('id_parqueadero')
                    ->from('ventas')
                    ->whereNotNull('id_parqueadero')
                    ->whereIn('tipo_operacion', ['venta', 'separacion']);
            })
            ->orderBy('numero')
            ->get();

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
            'parqueaderos' => $parqueaderos, // ✅ nuevo
            'inmueblePrecargado' => $inmueblePrecargado,
            'plazos_disponibles' => $plazos,

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
                'id_estado_inmueble' => 'nullable|exists:estados_inmueble,id_estado_inmueble',

                // ✅ Parqueadero adicional opcional
                'id_parqueadero'    => 'nullable|exists:parqueaderos,id_parqueadero',

                'cuota_inicial'     => 'nullable|numeric|min:0',
                'valor_separacion'  => 'nullable|numeric|min:0',
                'fecha_limite_separacion' => 'nullable|date|after_or_equal:today',

                'valor_total'       => 'nullable|numeric|min:0',
                'valor_restante'    => 'nullable|numeric|min:0',
                'descripcion'       => 'nullable|max:300',

                'plazo_cuota_inicial_meses' => 'nullable|integer|min:0',
                'frecuencia_cuota_inicial_meses' => 'nullable|integer|min:1',
            ]);

            if (($validated['tipo_operacion'] ?? null) === 'venta') {
                $validated['frecuencia_cuota_inicial_meses'] = (int)($validated['frecuencia_cuota_inicial_meses'] ?? 1);
            } else {
                $validated['frecuencia_cuota_inicial_meses'] = null;
                $validated['plazo_cuota_inicial_meses'] = null;
                $validated['cuota_inicial'] = null;
            }

            // ✅ Delegar al service (recalcula valor_total = inmueble + parqueadero y asigna parqueadero.id_apartamento)
            $venta = $this->ventaService->crearOperacion($validated);

            return redirect()
                ->route('admin.ventas.index')
                ->with('success', 'Operación registrada exitosamente.');
        });
    }

    /* ===========================================================
     *  EDIT
     * =========================================================== */

    public function edit(Request $request, $id)
    {
        $empleado = $request->user()->load('cargo');
        $venta = Venta::with(['apartamento', 'local', 'proyecto'])
            ->findOrFail($id);

        $proyecto = $venta->proyecto;
        $plazos = $this->calcularPlazosDisponibles($proyecto);

        // ✅ Parqueaderos para edit:
        // - libres (id_apartamento NULL)
        // - o el parqueadero actual de la venta (aunque ya tenga id_apartamento)
        // - y no reservados por otras ventas/separaciones (excluye esta venta)
        $idParqueaderoActual = $venta->id_parqueadero;

        $parqueaderos = Parqueadero::query()
            ->select('id_parqueadero', 'numero', 'tipo', 'id_proyecto', 'id_torre', 'precio')
            ->where(function ($q) use ($idParqueaderoActual) {
                $q->whereNull('id_apartamento');
                if ($idParqueaderoActual) {
                    $q->orWhere('id_parqueadero', $idParqueaderoActual);
                }
            })
            ->where(function ($q) use ($venta, $idParqueaderoActual) {
                $q->whereNotIn('id_parqueadero', function ($sq) use ($venta) {
                    $sq->select('id_parqueadero')
                        ->from('ventas')
                        ->whereNotNull('id_parqueadero')
                        ->whereIn('tipo_operacion', ['venta', 'separacion'])
                        ->where('id_venta', '!=', $venta->id_venta);
                });

                if ($idParqueaderoActual) {
                    $q->orWhere('id_parqueadero', $idParqueaderoActual);
                }
            })
            ->orderBy('numero')
            ->get();

        return Inertia::render('Admin/Ventas/Edit', [
            'venta' => $venta,
            'clientes' => Cliente::all(),
            'empleados' => Empleado::all(),
            'apartamentos' => Apartamento::with(['torre.proyecto'])->get(),
            'locales' => Local::with(['torre.proyecto'])->get(),
            'proyectos' => Proyecto::all(),
            'formasPago' => FormaPago::all(),
            'estadosInmueble' => EstadoInmueble::all(),
            'parqueaderos' => $parqueaderos, // ✅ nuevo
            'plazos_disponibles' => $plazos,
            'empleado' => $empleado,
        ]);
    }

    /* ===========================================================
     *  UPDATE
     * =========================================================== */

    public function update(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {

            $venta = Venta::lockForUpdate()->findOrFail($id);

            // Guardar estado anterior para liberar/asignar parqueadero.id_apartamento
            $oldParqueaderoId = $venta->id_parqueadero ? (int)$venta->id_parqueadero : null;
            $oldApartamentoId = $venta->id_apartamento ? (int)$venta->id_apartamento : null;

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

                // ✅ Parqueadero adicional opcional
                'id_parqueadero'    => 'nullable|exists:parqueaderos,id_parqueadero',

                'valor_total'       => 'nullable|numeric|min:0',
                'cuota_inicial'     => 'nullable|numeric|min:0',
                'valor_restante'    => 'nullable|numeric|min:0',
                'descripcion'       => 'nullable|string|max:300',
                'valor_separacion'  => 'nullable|numeric|min:0',
                'fecha_limite_separacion' => 'nullable|date|after_or_equal:today',
                'plazo_cuota_inicial_meses' => 'nullable|integer|min:0',
                'frecuencia_cuota_inicial_meses' => 'nullable|integer|min:1',
            ]);

            // Resolver inmueble nuevo (lock)
            $tipo = $validated['inmueble_tipo'];

            if ($tipo === 'apartamento') {
                $nuevo = Apartamento::with('torre.proyecto')->lockForUpdate()->findOrFail($validated['inmueble_id']);
                $validated['id_apartamento'] = $nuevo->id_apartamento;
                $validated['id_local'] = null;
            } else {
                $nuevo = Local::with('torre.proyecto')->lockForUpdate()->findOrFail($validated['inmueble_id']);
                $validated['id_local'] = $nuevo->id_local;
                $validated['id_apartamento'] = null;

                // No se permite parqueadero en local
                $validated['id_parqueadero'] = null;
            }

            unset($validated['inmueble_tipo'], $validated['inmueble_id']);

            // Estado destino según operación
            $estadoDestino = $validated['tipo_operacion'] === 'venta' ? 'Vendido' : 'Separado';
            $idEstadoDestino = EstadoInmueble::where('nombre', $estadoDestino)->value('id_estado_inmueble');
            $validated['id_estado_inmueble'] = $idEstadoDestino;

            // Validación y cálculo de parqueadero
            $idParqueadero = $validated['id_parqueadero'] ?? null;
            $precioParqueadero = 0.0;

            if (!empty($idParqueadero)) {
                if (!$validated['id_apartamento']) {
                    throw new \RuntimeException('El parqueadero adicional solo aplica para apartamentos.');
                }

                $p = Parqueadero::where('id_parqueadero', $idParqueadero)->lockForUpdate()->firstOrFail();

                // ✅ Permitir: libre (null) o asignado al MISMO apto de esta operación
                if (!empty($p->id_apartamento) && (int)$p->id_apartamento !== (int)$validated['id_apartamento']) {
                    throw new \RuntimeException('El parqueadero ya está asignado a otro apartamento.');
                }

                if (!empty($p->id_proyecto) && (int)$p->id_proyecto !== (int)$validated['id_proyecto']) {
                    throw new \RuntimeException('El parqueadero no pertenece al proyecto.');
                }

                // Debe estar libre en otras operaciones
                $ocupado = Venta::whereNotNull('id_parqueadero')
                    ->where('id_parqueadero', $idParqueadero)
                    ->where('id_venta', '!=', $venta->id_venta)
                    ->whereIn('tipo_operacion', ['venta', 'separacion'])
                    ->exists();

                if ($ocupado) {
                    throw new \RuntimeException('El parqueadero ya fue reservado en otra operación.');
                }

                $precioParqueadero = (float)($p->precio ?? 0);
            }

            // Recalcular valores backend
            $valorBaseInmueble = (float)($nuevo->valor_final ?? $nuevo->valor_total ?? 0);
            $validated['valor_base'] = $valorBaseInmueble;
            $validated['valor_total'] = $valorBaseInmueble + $precioParqueadero;

            if ($validated['tipo_operacion'] === 'venta') {
                $validated['frecuencia_cuota_inicial_meses'] = (int)($validated['frecuencia_cuota_inicial_meses'] ?? 1);
                $validated['valor_restante'] = max(0, (float)$validated['valor_total'] - (float)($validated['cuota_inicial'] ?? 0));
            } else {
                $validated['frecuencia_cuota_inicial_meses'] = null;
                $validated['plazo_cuota_inicial_meses'] = null;
                $validated['cuota_inicial'] = null;
                $validated['valor_restante'] = null;
            }

            // Actualizar venta
            $venta->update($validated);
            $venta->refresh();

            // Actualizar inmueble estado
            $nuevo->update(['id_estado_inmueble' => $idEstadoDestino]);

            // ===== Asignar/Liberar parqueadero.id_apartamento =====
            $newParqueaderoId = $venta->id_parqueadero ? (int)$venta->id_parqueadero : null;
            $newApartamentoId = $venta->id_apartamento ? (int)$venta->id_apartamento : null;

            // Si cambió o se quitó, liberar anterior
            if ($oldParqueaderoId && $oldParqueaderoId !== $newParqueaderoId) {
                Parqueadero::where('id_parqueadero', $oldParqueaderoId)
                    ->when($oldApartamentoId, fn($q) => $q->where('id_apartamento', $oldApartamentoId))
                    ->update(['id_apartamento' => null]);
            }

            // Si hay parqueadero actual, asignarlo al apartamento (idempotente)
            if ($newParqueaderoId && $newApartamentoId) {
                Parqueadero::where('id_parqueadero', $newParqueaderoId)
                    ->where(function ($q) use ($newApartamentoId) {
                        $q->whereNull('id_apartamento')
                            ->orWhere('id_apartamento', $newApartamentoId);
                    })
                    ->update(['id_apartamento' => $newApartamentoId]);
            }

            // Seguridad: si quedó como local, limpiar parqueadero
            if (!$venta->id_apartamento && $newParqueaderoId) {
                Parqueadero::where('id_parqueadero', $newParqueaderoId)->update(['id_apartamento' => null]);
                $venta->update(['id_parqueadero' => null]);
            }

            // Regenerar plan si es venta
            if ($venta->tipo_operacion === 'venta') {
                $plan = $venta->planAmortizacion;
                if ($plan) {
                    $plan->cuotas()->delete();
                    $plan->delete();
                }
                app(\App\Services\VentaService::class)->regenerarPlanCuotaInicial($venta);
            }

            // Recalcular precios
            app(\App\Services\PriceEngine::class)->recalcularProyectoPorVenta($venta);

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

            $venta = Venta::lockForUpdate()->findOrFail($id);

            $proyectoAsociado = Proyecto::find($venta->id_proyecto);

            // ✅ Liberar parqueadero adicional (si estaba asignado a este apto)
            if ($venta->id_parqueadero) {
                Parqueadero::where('id_parqueadero', (int)$venta->id_parqueadero)
                    ->when($venta->id_apartamento, fn($q) => $q->where('id_apartamento', (int)$venta->id_apartamento))
                    ->update(['id_apartamento' => null]);
            }

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

            if ($proyectoAsociado) {
                app(\App\Services\PriceEngine::class)->recalcularProyecto($proyectoAsociado);
            }

            return redirect()
                ->route('admin.ventas.index')
                ->with('success', 'Operación eliminada correctamente.');
        });
    }

    /* ===========================================================
     *  SEPARACIÓN: CANCELAR
     * =========================================================== */

    public function cancelarSeparacion($id)
    {
        return DB::transaction(function () use ($id) {

            $venta = Venta::with(['apartamento', 'local'])->lockForUpdate()->findOrFail($id);

            if (!$venta->esSeparacion()) {
                return back()->withErrors(['operacion' => 'Esta operación no es una separación.']);
            }

            $inmueble = $venta->id_apartamento ? $venta->apartamento : $venta->local;
            $proyecto = Proyecto::find($venta->id_proyecto);

            // ✅ Liberar parqueadero adicional (si estaba asignado a este apto)
            if ($venta->id_parqueadero) {
                Parqueadero::where('id_parqueadero', (int)$venta->id_parqueadero)
                    ->when($venta->id_apartamento, fn($q) => $q->where('id_apartamento', (int)$venta->id_apartamento))
                    ->update(['id_apartamento' => null]);
            }

            // Liberar inmueble
            $estadoDisponible = EstadoInmueble::where('nombre', 'Disponible')->first();

            if ($inmueble && $estadoDisponible) {
                $inmueble->update([
                    'id_estado_inmueble' => $estadoDisponible->id_estado_inmueble
                ]);
            }

            // Eliminar separación
            $venta->delete();

            // Recalcular bloque/precios
            if ($proyecto) {
                app(\App\Services\PriceEngine::class)->recalcularProyecto($proyecto);
            }

            return back()->with('success', 'Separación cancelada correctamente.');
        });
    }

    /* ===========================================================
     *  CONVERTIR SEPARACIÓN A VENTA (ADMIN - rápido)
     *  - Mantiene parqueadero (si existe)
     *  - Recalcula total (inmueble + parqueadero)
     *  - Regenera plan cuota inicial
     * =========================================================== */

    public function convertirEnVenta($id)
    {
        return DB::transaction(function () use ($id) {

            $venta = Venta::with(['apartamento', 'local'])->lockForUpdate()->findOrFail($id);

            if (!$venta->esSeparacion()) {
                return back()->withErrors(['operacion' => 'Esta operación no es una separación.']);
            }

            $proyecto = Proyecto::findOrFail($venta->id_proyecto);
            $inmueble = $venta->id_apartamento ? $venta->apartamento : $venta->local;

            if (!$inmueble) {
                return back()->withErrors(['operacion' => 'No se encontró el inmueble asociado.']);
            }

            $idEstadoVendido = EstadoInmueble::where('nombre', 'Vendido')->value('id_estado_inmueble');
            if (!$idEstadoVendido) {
                return back()->withErrors(['operacion' => 'No existe el estado "Vendido".']);
            }

            // Total = valor inmueble + parqueadero (si existe)
            $valorBase = (float)($inmueble->valor_final ?? $inmueble->valor_total ?? 0);
            $precioParq = 0.0;

            if ($venta->id_parqueadero && $venta->id_apartamento) {
                $p = Parqueadero::where('id_parqueadero', (int)$venta->id_parqueadero)->lockForUpdate()->first();

                if ($p) {
                    // permitir libre o asignado al mismo apto
                    if (!empty($p->id_apartamento) && (int)$p->id_apartamento !== (int)$venta->id_apartamento) {
                        return back()->withErrors(['operacion' => 'El parqueadero ya está asignado a otro apartamento.']);
                    }
                    $precioParq = (float)($p->precio ?? 0);
                }
            }

            $valorTotal = $valorBase + $precioParq;

            // Cuota inicial = lo pagado como separación
            $cuotaInicial = (float)($venta->valor_separacion ?? 0);

            // Validar reglas del proyecto con el total real
            app(\App\Services\VentaService::class)->validarVenta([
                'tipo_operacion' => 'venta',
                'valor_total' => $valorTotal,
                'cuota_inicial' => $cuotaInicial,
                'plazo_cuota_inicial_meses' => (int)($venta->plazo_cuota_inicial_meses ?? 1),
                'frecuencia_cuota_inicial_meses' => (int)($venta->frecuencia_cuota_inicial_meses ?? 1),
            ], $proyecto);

            $venta->update([
                'tipo_operacion' => 'venta',
                'fecha_venta' => now()->toDateString(),
                'cuota_inicial' => $cuotaInicial,
                'valor_base' => $valorBase,
                'valor_total' => $valorTotal,
                'valor_restante' => max(0, $valorTotal - $cuotaInicial),
                'valor_separacion' => null,
                'fecha_limite_separacion' => null,
                'id_estado_inmueble' => $idEstadoVendido,
            ]);

            // Asegurar parqueadero.id_apartamento (si aplica)
            if ($venta->id_parqueadero && $venta->id_apartamento) {
                Parqueadero::where('id_parqueadero', (int)$venta->id_parqueadero)
                    ->where(function ($q) use ($venta) {
                        $q->whereNull('id_apartamento')
                            ->orWhere('id_apartamento', (int)$venta->id_apartamento);
                    })
                    ->update(['id_apartamento' => (int)$venta->id_apartamento]);
            }

            // Actualizar inmueble a Vendido
            $inmueble->update(['id_estado_inmueble' => $idEstadoVendido]);

            // Regenerar plan cuota inicial
            app(\App\Services\VentaService::class)->regenerarPlanCuotaInicial($venta);

            // Recalcular precios del proyecto
            app(\App\Services\PriceEngine::class)->recalcularProyecto($proyecto);

            return redirect()
                ->route('admin.ventas.index')
                ->with('success', 'La separación ahora es una venta.');
        });
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
