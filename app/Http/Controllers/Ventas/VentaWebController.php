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
use App\Models\TipoCliente;
use App\Models\TipoDocumento;
use App\Models\Parqueadero;
use App\Services\VentaService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class VentaWebController extends Controller
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
        $proyecto = Proyecto::first(); // temporal para debug, escoger el proyecto real

        $ventasActivas = Venta::where('id_proyecto', $proyecto->id_proyecto)
            ->whereIn('tipo_operacion', ['venta', 'separacion'])
            ->count();

        $pe = new \App\Services\PriceEngine();
        $peBloque = $pe->obtenerBloqueActual($proyecto);
        $peFactor = $pe->calcularFactorAumento($proyecto, $peBloque);
        $pePoliticas = $proyecto->politicasPrecio()->get()->toArray();

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
            'parqueadero', // ✅ nuevo
        ])->orderBy('fecha_venta', 'desc')->get();

        return Inertia::render('Ventas/Venta/Index', [
            'ventas' => $ventas,
            'empleado' => $empleado,
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

        // ✅ Parqueaderos disponibles para venta adicional:
        // Regla: parqueadero "adicional" = id_apartamento NULL
        // Además: que no esté ya asociado a una venta (id_parqueadero en ventas)
        $parqueaderos = Parqueadero::query()
            ->select('id_parqueadero', 'numero', 'tipo', 'id_proyecto', 'id_torre', 'precio')
            ->whereNull('id_apartamento')
            ->whereNotIn('id_parqueadero', function ($q) {
                $q->select('id_parqueadero')
                    ->from('ventas')
                    ->whereNotNull('id_parqueadero');
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

        $tiposCliente = TipoCliente::orderBy('tipo_cliente')->get();
        $tiposDocumento = TipoDocumento::orderBy('tipo_documento')->get();

        return Inertia::render('Ventas/Venta/Create', [
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
            'tiposCliente' => $tiposCliente,
            'tiposDocumento' => $tiposDocumento,
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

            $venta = $this->ventaService->crearOperacion($validated);

            return redirect()
                ->route('ventas.show', $venta->id_venta)
                ->with('success', 'Operación registrada exitosamente.');
        });
    }

    /* ===========================================================
     *  SHOW
     * =========================================================== */

    public function show(Request $request, $id)
    {
        $empleado = $request->user()->load('cargo');
        $venta = Venta::with([
            'cliente',
            'empleado',
            'proyecto',
            'formaPago',
            'proyecto.ubicacion',
            'proyecto.zonasSociales',
            'apartamento.estadoInmueble',
            'apartamento.torre',
            'apartamento.pisoTorre',
            'apartamento.tipoApartamento',
            'apartamento.parqueaderos',
            'local.estadoInmueble',
            'local.torre',
            'local.pisoTorre',
            'parqueadero', // ✅ nuevo
            'planAmortizacion.cuotas',
            'pagos',
        ])->findOrFail($id);

        $imagenTipoAptoUrl = null;

        if ($venta->apartamento?->tipoApartamento?->imagen) {
            $path = $venta->apartamento->tipoApartamento->imagen;
            $imagenTipoAptoUrl = asset('storage/' . $path);
        }

        return Inertia::render('Ventas/Venta/Show', [
            'venta' => $venta,
            'imagenTipoAptoUrl' => $imagenTipoAptoUrl,
            'empleado' => $empleado,
        ]);
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

        // Parqueadero actual
        $idParqueaderoActual = $venta->id_parqueadero;

        $parqueaderos = Parqueadero::query()
            ->select('id_parqueadero', 'numero', 'tipo', 'id_proyecto', 'id_torre', 'precio')
            ->where(function ($q) use ($idParqueaderoActual) {
                // A) parqueaderos libres (adicionales)
                $q->whereNull('id_apartamento');

                // B) o el parqueadero actual de esta venta (aunque ya tenga id_apartamento)
                if ($idParqueaderoActual) {
                    $q->orWhere('id_parqueadero', $idParqueaderoActual);
                }
            })
            ->where(function ($q) use ($venta) {
                // disponibles = no reservados en otras ventas/separaciones
                $q->whereNotIn('id_parqueadero', function ($sq) use ($venta) {
                    $sq->select('id_parqueadero')
                        ->from('ventas')
                        ->whereNotNull('id_parqueadero')
                        ->whereIn('tipo_operacion', ['venta', 'separacion'])
                        ->where('id_venta', '!=', $venta->id_venta); // ✅ excluir esta venta
                });

                // permitir el actual de esta venta
                if ($venta->id_parqueadero) {
                    $q->orWhere('id_parqueadero', $venta->id_parqueadero);
                }
            })
            ->orderBy('numero')
            ->get();

        return Inertia::render('Ventas/Venta/Edit', [
            'venta' => $venta,
            'clientes' => Cliente::all(),
            'empleados' => Empleado::all(),
            'apartamentos' => Apartamento::with(['torre.proyecto'])->get(),
            'locales' => Local::with(['torre.proyecto'])->get(),
            'proyectos' => Proyecto::all(),
            'formasPago' => FormaPago::all(),
            'estadosInmueble' => EstadoInmueble::all(),
            'parqueaderos' => $parqueaderos,
            'plazos_disponibles' => $plazos,
            'empleado' => $empleado,
        ]);
    }

    public function update(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {

            $venta = Venta::lockForUpdate()->findOrFail($id);

            // Guardar estado anterior para liberar/asignar
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

            // Validación y reserva de parqueadero (si aplica)
            $idParqueadero = $validated['id_parqueadero'] ?? null;
            $precioParqueadero = 0.0;

            if (!empty($idParqueadero)) {
                if (!$validated['id_apartamento']) {
                    throw new \RuntimeException('El parqueadero adicional solo aplica para apartamentos.');
                }

                $p = Parqueadero::where('id_parqueadero', $idParqueadero)->lockForUpdate()->firstOrFail();

                // ✅ Validación de "adicional":
                // Permitimos:
                // - id_apartamento NULL (aún adicional libre)
                // - o id_apartamento = el apartamento de ESTA venta (ya quedó asignado por la separación/venta)
                if (!empty($p->id_apartamento) && (int)$p->id_apartamento !== (int)$validated['id_apartamento']) {
                    throw new \RuntimeException('El parqueadero seleccionado no es adicional o ya está asignado a otro apartamento.');
                }

                if (!empty($p->id_proyecto) && (int)$p->id_proyecto !== (int)$validated['id_proyecto']) {
                    throw new \RuntimeException('El parqueadero no pertenece al proyecto.');
                }

                // Debe estar libre o ser el mismo ya asignado a esta venta
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

            // ======= ACTUALIZAR VENTA =======
            $venta->update($validated);
            $venta->refresh();

            // Actualizar inmueble estado
            $nuevo->update(['id_estado_inmueble' => $idEstadoDestino]);

            // ======= ASIGNAR/LIBERAR PARQUEADERO EN TABLA PARQUEADEROS =======
            $newParqueaderoId = $venta->id_parqueadero ? (int)$venta->id_parqueadero : null;
            $newApartamentoId = $venta->id_apartamento ? (int)$venta->id_apartamento : null;

            // 1) Si se quitó o cambió parqueadero → liberar el anterior (si estaba ligado a ese apto anterior)
            if ($oldParqueaderoId && $oldParqueaderoId !== $newParqueaderoId) {
                Parqueadero::where('id_parqueadero', $oldParqueaderoId)
                    ->when($oldApartamentoId, fn($q) => $q->where('id_apartamento', $oldApartamentoId))
                    ->update(['id_apartamento' => null]);
            }

            // 2) Si hay parqueadero actual y apartamento → asignarlo al apartamento (si estaba libre)
            if ($newParqueaderoId && $newApartamentoId) {
                Parqueadero::where('id_parqueadero', $newParqueaderoId)
                    ->where(function ($q) use ($newApartamentoId) {
                        $q->whereNull('id_apartamento')
                            ->orWhere('id_apartamento', $newApartamentoId); // idempotente si ya estaba asignado
                    })
                    ->update(['id_apartamento' => $newApartamentoId]);
            }

            // 3) Si la operación quedó como local (sin apartamento) asegurar liberar el parqueadero actual (por seguridad)
            if (!$venta->id_apartamento && $newParqueaderoId) {
                Parqueadero::where('id_parqueadero', $newParqueaderoId)->update(['id_apartamento' => null]);
                $venta->update(['id_parqueadero' => null]);
            }

            // ======= REGENERAR PLAN SI ES VENTA =======
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
            $proyectoAsociado = Proyecto::find($venta->id_proyecto);

            // ✅ liberar parqueadero adicional (solo si la venta lo tenía)
            if ($venta->id_parqueadero) {
                // En este diseño, la disponibilidad se controla por ventas.id_parqueadero,
                // así que basta con borrar la venta.
                // Si en tu futuro decides "marcar" parqueadero, aquí también lo liberarías.
            }

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

            if ($venta->id_parqueadero) {
                app(\App\Services\VentaService::class)->liberarParqueaderoDeApartamento(
                    (int)$venta->id_parqueadero,
                    $venta->id_apartamento ? (int)$venta->id_apartamento : null
                );
            }

            $venta->delete();

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

        $estadoDisponible = EstadoInmueble::where('nombre', 'Disponible')->first();

        $inmueble->update([
            'id_estado_inmueble' => $estadoDisponible->id_estado_inmueble
        ]);

        if ($venta->id_parqueadero) {
            // liberar (solo si estaba asignado a este apto)
            app(\App\Services\VentaService::class)->liberarParqueaderoDeApartamento(
                (int)$venta->id_parqueadero,
                $venta->id_apartamento ? (int)$venta->id_apartamento : null
            );
        }

        // ✅ al borrar la separación, el parqueadero vuelve a estar disponible automáticamente
        $venta->delete();

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
            'estado_operacion' => 'convertida',
            'fecha_limite_separacion' => null,
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

    public function convertirForm($id)
    {
        $venta = Venta::with([
            'cliente',
            'empleado',
            'proyecto',
            'formaPago',
            'planAmortizacion.cuotas',
            // Apartamento completo (para mostrar info y, si aplica, usar tipo)
            'apartamento.estadoInmueble',
            'apartamento.torre',
            'apartamento.pisoTorre',
            'apartamento.tipoApartamento',
            // Local completo
            'local.estadoInmueble',
            'local.torre',
            'local.pisoTorre',
        ])->findOrFail($id);

        // 1) Solo separaciones vigentes
        if (!$venta->esSeparacion()) {
            return back()->withErrors(['operacion' => 'Esta operación no es una separación.']);
        }

        if (($venta->estado_operacion ?? null) !== 'vigente') {
            return back()->withErrors(['operacion' => 'Solo separaciones vigentes pueden convertirse.']);
        }

        // 2) Validar inmueble asociado
        $inmueble = $venta->id_apartamento ? $venta->apartamento : $venta->local;
        if (!$inmueble) {
            return back()->withErrors(['operacion' => 'No se encontró el inmueble asociado.']);
        }

        // 3) Proyecto y plazos
        $proyecto = $venta->proyecto ?: Proyecto::find($venta->id_proyecto);
        $plazos = $proyecto ? $this->calcularPlazosDisponibles($proyecto) : [];

        // 4) Datasets para reutilizar la UI de Create (como pidió la vista Convertir.vue)
        $clientes = Cliente::orderBy('nombre')->get();
        $proyectos = Proyecto::orderBy('nombre')->get(); // opcional, pero útil para resumen/consistencia
        $formasPago = FormaPago::orderBy('forma_pago')->get();
        $estadosInmueble = EstadoInmueble::orderBy('nombre')->get();
        $tiposCliente = TipoCliente::orderBy('tipo_cliente')->get();
        $tiposDocumento = TipoDocumento::orderBy('tipo_documento')->get();

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

        return Inertia::render('Ventas/Venta/Convertir', [
            'venta' => $venta,

            // Lo que la vista necesita para selects/validaciones/resumen
            'clientes' => $clientes,
            'proyectos' => $proyectos,
            'formasPago' => $formasPago,
            'estadosInmueble' => $estadosInmueble,
            'plazos_disponibles' => $plazos,
            'tiposCliente' => $tiposCliente,
            'tiposDocumento' => $tiposDocumento,
            'parqueaderos' => $parqueaderos,
        ]);
    }

    public function convertirStore(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {

            $venta = Venta::with(['apartamento', 'local'])->lockForUpdate()->findOrFail($id);

            if (!$venta->esSeparacion()) {
                return back()->withErrors(['operacion' => 'Esta operación no es una separación.']);
            }

            if ($venta->estado_operacion !== 'vigente') {
                return back()->withErrors(['operacion' => 'Solo separaciones vigentes pueden convertirse.']);
            }

            $validated = $request->validate([
                'id_forma_pago' => 'required|exists:formas_pago,id_forma_pago',
                'cuota_inicial' => 'required|numeric|min:0',
                'plazo_cuota_inicial_meses' => 'required|integer|min:1',
                'frecuencia_cuota_inicial_meses' => 'required|integer|min:1',
                'descripcion' => 'nullable|string|max:300',
                'id_parqueadero' => 'nullable|exists:parqueaderos,id_parqueadero',
            ]);

            $validated['fecha_venta'] = now()->toDateString();

            // Si el form no envía id_parqueadero, conservar el de la separación
            if (!array_key_exists('id_parqueadero', $validated) || $validated['id_parqueadero'] === null || $validated['id_parqueadero'] === '') {
                $validated['id_parqueadero'] = $venta->id_parqueadero;
            }

            $proyecto = Proyecto::findOrFail($venta->id_proyecto);

            $inmueble = $venta->id_apartamento ? $venta->apartamento : $venta->local;
            if (!$inmueble) {
                return back()->withErrors(['operacion' => 'No se encontró el inmueble asociado.']);
            }

            $valorBase = (float)($inmueble->valor_final ?? $inmueble->valor_total ?? 0);

            // Guardar anteriores para liberar/asignar
            $oldParqueaderoId = $venta->id_parqueadero ? (int)$venta->id_parqueadero : null;
            $oldApartamentoId = $venta->id_apartamento ? (int)$venta->id_apartamento : null;

            $precioParq = 0.0;

            if (!empty($validated['id_parqueadero'])) {

                // parqueadero adicional solo aplica a apartamentos
                if (!$venta->id_apartamento) {
                    return back()->withErrors(['operacion' => 'Parqueadero adicional solo aplica a apartamentos.']);
                }

                $p = Parqueadero::where('id_parqueadero', $validated['id_parqueadero'])
                    ->lockForUpdate()
                    ->firstOrFail();

                // ✅ Validación correcta con tu nuevo esquema:
                // permitir libre (null) o asignado al MISMO apto de esta venta
                if (!empty($p->id_apartamento) && (int)$p->id_apartamento !== (int)$venta->id_apartamento) {
                    return back()->withErrors(['operacion' => 'El parqueadero ya está asignado a otro apartamento.']);
                }

                if (!empty($p->id_proyecto) && (int)$p->id_proyecto !== (int)$venta->id_proyecto) {
                    return back()->withErrors(['operacion' => 'El parqueadero no pertenece al proyecto.']);
                }

                // Debe estar libre en otras operaciones
                $ocupado = Venta::whereNotNull('id_parqueadero')
                    ->where('id_parqueadero', $validated['id_parqueadero'])
                    ->where('id_venta', '!=', $venta->id_venta)
                    ->whereIn('tipo_operacion', ['venta', 'separacion'])
                    ->exists();

                if ($ocupado) {
                    return back()->withErrors(['operacion' => 'El parqueadero ya fue reservado por otra operación.']);
                }

                $precioParq = (float)($p->precio ?? 0);
            }

            $valorTotal = $valorBase + $precioParq;

            // Validación negocio con total real
            app(\App\Services\VentaService::class)->validarVenta([
                ...$validated,
                'tipo_operacion' => 'venta',
                'valor_total' => $valorTotal,
            ], $proyecto);

            $valorRestante = max(0, $valorTotal - (float)$validated['cuota_inicial']);

            $idEstadoVendido = EstadoInmueble::where('nombre', 'Vendido')->value('id_estado_inmueble');
            if (!$idEstadoVendido) {
                return back()->withErrors(['operacion' => 'No existe el estado "Vendido".']);
            }

            // ====== UPDATE venta ======
            $venta->update([
                'tipo_operacion' => 'venta',
                'fecha_limite_separacion' => null,
                'estado_operacion' => 'convertida',
                'id_forma_pago' => $validated['id_forma_pago'],
                'fecha_venta' => $validated['fecha_venta'],
                'valor_base' => $valorBase,
                'valor_total' => $valorTotal,
                'cuota_inicial' => $validated['cuota_inicial'],
                'valor_restante' => $valorRestante,
                'plazo_cuota_inicial_meses' => $validated['plazo_cuota_inicial_meses'],
                'frecuencia_cuota_inicial_meses' => $validated['frecuencia_cuota_inicial_meses'],
                'descripcion' => $validated['descripcion'] ?? $venta->descripcion,
                'valor_separacion' => null,
                'id_parqueadero' => $validated['id_parqueadero'] ?: null,
            ]);

            $venta->refresh();

            // ====== Asignar/Liberar parqueadero.id_apartamento ======
            $newParqueaderoId = $venta->id_parqueadero ? (int)$venta->id_parqueadero : null;
            $newApartamentoId = $venta->id_apartamento ? (int)$venta->id_apartamento : null;

            // Si cambió o se quitó, liberar el anterior
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

            // Actualizar inmueble a Vendido
            $inmueble->update(['id_estado_inmueble' => $idEstadoVendido]);

            // Regenerar plan cuota inicial
            app(\App\Services\VentaService::class)->regenerarPlanCuotaInicial($venta);

            // Recalcular precios del proyecto
            app(\App\Services\PriceEngine::class)->recalcularProyectoPorVenta($venta);

            return redirect()
                ->route('ventas.show', $venta->id_venta)
                ->with('success', 'La separación fue convertida a venta correctamente.');
        });
    }
}
