<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\Apartamento;
use App\Models\Local;
use App\Models\Proyecto;
use App\Models\EstadoInmueble;
use App\Models\Parqueadero;
use App\Models\PlanPagoProyecto;
use App\Models\PlanAmortizacionVenta;
use App\Models\PlanAmortizacionCuota;
use App\Services\ProyectoPricingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class VentaService
{
    public const PLAN_DEFAULT_PROYECTO = '__condiciones_proyecto__';

    public function __construct(
        protected ProyectoPricingService $pricingService
    ) {}

    public function crearOperacion(array $data): Venta
    {
        return DB::transaction(function () use ($data) {
            $proyecto = Proyecto::with([
                'planesPago' => function ($query) {
                    $query->activos()
                        ->orderBy('orden')
                        ->orderBy('id_plan_pago_proyecto');
                },
            ])->findOrFail($data['id_proyecto']);

            [$inmueble, $valorBaseInmueble] = $this->resolverInmuebleConBloqueo($data);
            $parqueadero = $this->resolverParqueaderoAdicional($data, $proyecto);

            $precioParqueadero = $parqueadero ? (float) ($parqueadero->precio ?? 0) : 0.0;
            $valorTotalBruto = $valorBaseInmueble + $precioParqueadero;

            $plan = $this->resolverPlanPago($proyecto, $data['id_plan_pago_proyecto'] ?? null);
            $resumen = $this->calcularResumenEconomico($valorTotalBruto, $plan);

            $data['valor_base'] = $valorBaseInmueble;
            $data['valor_total_sin_descuento'] = $valorTotalBruto;
            $data['valor_descuento'] = $resumen['valor_descuento'];
            $data['valor_total'] = $resumen['valor_total'];
            $data['cuota_inicial'] = $data['tipo_operacion'] === Venta::TIPO_VENTA
                ? $resumen['cuota_inicial']
                : null;
            $data['saldo_cuota_inicial'] = $data['tipo_operacion'] === Venta::TIPO_VENTA
                ? $resumen['saldo_cuota_inicial']
                : null;
            $data['valor_restante'] = $data['tipo_operacion'] === Venta::TIPO_VENTA
                ? $resumen['valor_restante']
                : null;
            $data['valor_separacion'] = $resumen['valor_separacion'];

            $data['id_plan_pago_proyecto'] = $plan['id_plan_pago_proyecto'] ?? null;
            $data['plan_pago_codigo'] = $plan['codigo'];
            $data['plan_pago_nombre'] = $plan['nombre'];
            $data['plan_pago_tipo'] = $plan['tipo_plan'];
            $data['plan_pago_snapshot'] = $plan;

            $data['plazo_cuota_inicial_meses'] = $this->resolverPlazoVenta($data, $plan);
            $data['frecuencia_cuota_inicial_meses'] = $this->resolverFrecuenciaVenta($data, $plan);

            if ($data['tipo_operacion'] !== Venta::TIPO_VENTA) {
                $data['plazo_cuota_inicial_meses'] = null;
                $data['frecuencia_cuota_inicial_meses'] = null;
                $data['cuotas_manual_ci'] = null;
            }

            if ($data['tipo_operacion'] === Venta::TIPO_VENTA) {
                $this->validarVentaConPlan($data, $proyecto, $plan, $resumen);
            } else {
                $this->validarSeparacionConPlan($data, $proyecto, $plan, $resumen);
            }

            $this->mapearInmuebleEnData($data);

            $this->validarOperacionDuplicada($data);

            $venta = Venta::create($data);

            $this->asignarParqueaderoAApartamento(
                $venta->id_parqueadero ? (int) $venta->id_parqueadero : null,
                $venta->id_apartamento ? (int) $venta->id_apartamento : null
            );

            $estadoDestino = $venta->tipo_operacion === Venta::TIPO_VENTA ? 'Vendido' : 'Separado';
            $idEstadoDestino = EstadoInmueble::whereRaw('LOWER(nombre) = ?', [strtolower($estadoDestino)])
                ->value('id_estado_inmueble');

            if (!$idEstadoDestino) {
                throw new \RuntimeException("No existe el estado de inmueble {$estadoDestino}.");
            }

            $inmueble->update(['id_estado_inmueble' => $idEstadoDestino]);

            if ($venta->tipo_operacion === Venta::TIPO_VENTA) {
                $this->generarPlanCuotaInicial($venta);
            }

            app(\App\Services\PriceEngine::class)->recalcularProyecto($proyecto);

            session([
                'debug_venta' => [
                    'tipo' => $venta->tipo_operacion,
                    'plan' => $venta->plan_pago_nombre,
                    'valor_base' => $venta->valor_base,
                    'valor_total_sin_descuento' => $venta->valor_total_sin_descuento,
                    'valor_descuento' => $venta->valor_descuento,
                    'valor_total' => $venta->valor_total,
                    'cuota_inicial' => $venta->cuota_inicial,
                    'valor_separacion' => $venta->valor_separacion,
                    'valor_restante' => $venta->valor_restante,
                    'estado_inmueble' => $estadoDestino,
                ],
            ]);

            return $venta;
        });
    }

    public function actualizarVentaConPlan(Venta $venta, array $data): Venta
    {
        return DB::transaction(function () use ($venta, $data) {
            $venta->refresh();

            $proyecto = Proyecto::with([
                'planesPago' => function ($query) {
                    $query->activos()
                        ->orderBy('orden')
                        ->orderBy('id_plan_pago_proyecto');
                },
            ])->findOrFail($venta->id_proyecto);

            $valorBase = (float) ($venta->apartamento?->valor_final
                ?? $venta->apartamento?->valor_total
                ?? $venta->local?->valor_total
                ?? $venta->valor_base
                ?? 0);

            $precioParqueadero = 0.0;

            if (!empty($data['id_parqueadero'])) {
                $parqueadero = Parqueadero::where('id_parqueadero', $data['id_parqueadero'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($venta->id_apartamento && !empty($parqueadero->id_apartamento) && (int) $parqueadero->id_apartamento !== (int) $venta->id_apartamento) {
                    throw ValidationException::withMessages([
                        'id_parqueadero' => 'El parqueadero ya está asignado a otro apartamento.',
                    ]);
                }

                if (!empty($parqueadero->id_proyecto) && (int) $parqueadero->id_proyecto !== (int) $venta->id_proyecto) {
                    throw ValidationException::withMessages([
                        'id_parqueadero' => 'El parqueadero no pertenece al proyecto.',
                    ]);
                }

                $ocupado = Venta::whereNotNull('id_parqueadero')
                    ->where('id_parqueadero', $data['id_parqueadero'])
                    ->where('id_venta', '!=', $venta->id_venta)
                    ->whereIn('tipo_operacion', ['venta', 'separacion'])
                    ->exists();

                if ($ocupado) {
                    throw ValidationException::withMessages([
                        'id_parqueadero' => 'El parqueadero ya fue reservado por otra operación.',
                    ]);
                }

                $precioParqueadero = (float) ($parqueadero->precio ?? 0);
            }

            $valorTotalBruto = $valorBase + $precioParqueadero;

            $plan = $this->resolverPlanPago(
                $proyecto,
                $data['id_plan_pago_proyecto'] ?? $venta->id_plan_pago_proyecto ?? null
            );

            $resumen = $this->calcularResumenEconomico($valorTotalBruto, $plan);

            $data['valor_base'] = $valorBase;
            $data['valor_total_sin_descuento'] = $valorTotalBruto;
            $data['valor_descuento'] = $resumen['valor_descuento'];
            $data['valor_total'] = $resumen['valor_total'];
            $data['cuota_inicial'] = $resumen['cuota_inicial'];
            $data['saldo_cuota_inicial'] = $resumen['saldo_cuota_inicial'];
            $data['valor_restante'] = $resumen['valor_restante'];
            $data['valor_separacion'] = $resumen['valor_separacion'];

            $data['id_plan_pago_proyecto'] = $plan['id_plan_pago_proyecto'] ?? null;
            $data['plan_pago_codigo'] = $plan['codigo'];
            $data['plan_pago_nombre'] = $plan['nombre'];
            $data['plan_pago_tipo'] = $plan['tipo_plan'];
            $data['plan_pago_snapshot'] = $plan;

            $data['plazo_cuota_inicial_meses'] = $this->resolverPlazoVenta($data, $plan);
            $data['frecuencia_cuota_inicial_meses'] = $this->resolverFrecuenciaVenta($data, $plan);

            $this->validarVentaConPlan($data, $proyecto, $plan, $resumen);

            $venta->update($data);
            $venta->refresh();

            $this->regenerarPlanCuotaInicial($venta);

            return $venta;
        });
    }

    public function convertirSeparacionEnVenta(Venta $venta, array $data): Venta
    {
        return DB::transaction(function () use ($venta, $data) {
            if ($venta->tipo_operacion !== Venta::TIPO_SEPARACION) {
                throw ValidationException::withMessages([
                    'tipo_operacion' => 'Solo se pueden convertir separaciones vigentes.',
                ]);
            }

            $data['tipo_operacion'] = Venta::TIPO_VENTA;
            $data['fecha_limite_separacion'] = null;
            $data['estado_operacion'] = 'convertida';

            if (empty($data['id_plan_pago_proyecto'])) {
                $data['id_plan_pago_proyecto'] = $venta->id_plan_pago_proyecto;
            }

            $venta = $this->actualizarVentaConPlan($venta, $data);

            $idEstadoVendido = EstadoInmueble::whereRaw('LOWER(nombre) = ?', ['vendido'])
                ->value('id_estado_inmueble');

            if (!$idEstadoVendido) {
                throw ValidationException::withMessages([
                    'id_estado_inmueble' => 'No existe el estado "Vendido".',
                ]);
            }

            $inmueble = $venta->apartamento ?: $venta->local;

            if ($inmueble) {
                $inmueble->update(['id_estado_inmueble' => $idEstadoVendido]);
            }

            return $venta;
        });
    }

    public function resolverPlanPago(Proyecto $proyecto, $idPlan = null): array
    {
        $planesActivos = $proyecto->planesPago()
            ->activos()
            ->orderBy('orden')
            ->orderBy('id_plan_pago_proyecto')
            ->get();

        $usarDefault = empty($idPlan)
            || $idPlan === self::PLAN_DEFAULT_PROYECTO
            || $idPlan === 'condiciones_proyecto';

        if (!$usarDefault) {
            $plan = $planesActivos->firstWhere('id_plan_pago_proyecto', (int) $idPlan);

            if (!$plan) {
                throw ValidationException::withMessages([
                    'id_plan_pago_proyecto' => 'El plan seleccionado no pertenece al proyecto o no está activo.',
                ]);
            }

            return $this->normalizarPlanReal($plan);
        }

        if ($planesActivos->isNotEmpty() && $usarDefault) {
            throw ValidationException::withMessages([
                'id_plan_pago_proyecto' => 'Debes seleccionar un plan de venta activo para este proyecto.',
            ]);
        }

        return $this->planDefaultProyecto($proyecto);
    }

    public function calcularResumenEconomico(float $valorTotalBruto, array $plan): array
    {
        $porcentajeCuotaInicial = (float) ($plan['porcentaje_cuota_inicial'] ?? 0);
        $porcentajeEscritura = (float) ($plan['porcentaje_escritura'] ?? 0);
        $valorSeparacion = (float) ($plan['valor_separacion'] ?? 0);

        $cuotaInicialBruta = round($valorTotalBruto * ($porcentajeCuotaInicial / 100));
        $valorDescuento = $this->calcularDescuento($valorTotalBruto, $cuotaInicialBruta, $plan);
        $valorTotalCotizado = $valorTotalBruto;

        $cuotaInicial = $cuotaInicialBruta;
        $saldoCuotaInicial = max($cuotaInicial - $valorSeparacion, 0);
        $valorRestante = max($valorTotalBruto - $cuotaInicial, 0);
        $saldoPagoDiferido = 0;

        if (($plan['tipo_descuento'] ?? 'ninguno') !== 'ninguno' && ($plan['base_descuento'] ?? 'ninguna') === 'precio_total') {
            $valorTotalCotizado = max($valorTotalBruto - $valorDescuento, 0);

            if ($this->esPlanCuotaInicialMensual($plan)) {
                $cuotaInicial = round($valorTotalCotizado * ($porcentajeCuotaInicial / 100));
                $saldoCuotaInicial = max($cuotaInicial - $valorSeparacion, 0);
                $valorRestante = max($valorTotalCotizado - $cuotaInicial, 0);
            }

            if (($plan['tipo_plan'] ?? null) === 'pago_total_diferido') {
                $cuotaInicial = $valorSeparacion;
                $saldoCuotaInicial = 0;
                $saldoPagoDiferido = max($valorTotalCotizado - $valorSeparacion, 0);
                $valorRestante = $saldoPagoDiferido;
            }
        }

        if (($plan['tipo_descuento'] ?? 'ninguno') !== 'ninguno' && ($plan['base_descuento'] ?? 'ninguna') === 'cuota_inicial') {
            $cuotaInicial = max($cuotaInicialBruta - $valorDescuento, 0);
            $saldoCuotaInicial = max($cuotaInicial - $valorSeparacion, 0);

            $valorEscritura = round($valorTotalBruto * ($porcentajeEscritura / 100));
            $valorTotalCotizado = $cuotaInicial + $valorEscritura;
            $valorRestante = max($valorTotalCotizado - $cuotaInicial, 0);
        }

        if (($plan['tipo_descuento'] ?? 'ninguno') === 'ninguno' && ($plan['tipo_plan'] ?? null) === 'pago_total_diferido') {
            $cuotaInicial = $valorSeparacion;
            $saldoCuotaInicial = 0;
            $saldoPagoDiferido = max($valorTotalBruto - $valorSeparacion, 0);
            $valorRestante = $saldoPagoDiferido;
        }

        return [
            'valor_total_sin_descuento' => round($valorTotalBruto),
            'valor_descuento' => round($valorDescuento),
            'valor_total' => round($valorTotalCotizado),
            'valor_separacion' => round($valorSeparacion),
            'cuota_inicial' => round($cuotaInicial),
            'saldo_cuota_inicial' => round($saldoCuotaInicial),
            'valor_restante' => round($valorRestante),
            'saldo_pago_diferido' => round($saldoPagoDiferido),
        ];
    }

    protected function calcularDescuento(float $valorTotal, float $cuotaInicialBruta, array $plan): float
    {
        if (($plan['tipo_descuento'] ?? 'ninguno') === 'ninguno') {
            return 0;
        }

        $valorDescuento = (float) ($plan['valor_descuento'] ?? 0);

        if (($plan['tipo_descuento'] ?? null) === 'valor_fijo') {
            return $valorDescuento;
        }

        if (($plan['tipo_descuento'] ?? null) === 'porcentaje') {
            if (($plan['base_descuento'] ?? null) === 'cuota_inicial') {
                return $cuotaInicialBruta * ($valorDescuento / 100);
            }

            if (($plan['base_descuento'] ?? null) === 'precio_total') {
                return $valorTotal * ($valorDescuento / 100);
            }
        }

        return 0;
    }

    protected function generarPlanCuotaInicial(Venta $venta): void
    {
        if ($venta->tipo_operacion !== Venta::TIPO_VENTA) {
            return;
        }

        $planData = $venta->plan_pago_snapshot ?: $this->planDefaultProyecto($venta->proyecto);
        $fechaInicio = Carbon::parse($venta->fecha_venta)->startOfDay();

        $plan = PlanAmortizacionVenta::create([
            'id_venta' => $venta->id_venta,
            'tipo_plan' => $venta->plan_pago_tipo ?: 'cuota_inicial',
            'valor_interes_anual' => 0,
            'plazo_meses' => (int) ($venta->plazo_cuota_inicial_meses ?? 0),
            'fecha_inicio' => $fechaInicio,
            'observacion' => $venta->plan_pago_nombre
                ? "Plan de pagos generado desde {$venta->plan_pago_nombre}"
                : 'Plan de pagos generado desde condiciones económicas del proyecto',
        ]);

        $numeroCuota = 1;
        $valorSeparacion = (float) ($venta->valor_separacion ?? 0);
        $saldoCuotaInicial = (float) ($venta->saldo_cuota_inicial ?? 0);
        $valorRestante = (float) ($venta->valor_restante ?? 0);

        if ($valorSeparacion > 0) {
            $this->crearCuota($plan, $numeroCuota, $fechaInicio, 'Separación', $valorSeparacion, $saldoCuotaInicial);
            $numeroCuota++;
        }

        if ($this->esPlanCuotaInicialMensual($planData)) {
            $plazo = (int) ($venta->plazo_cuota_inicial_meses ?? 0);
            $frecuencia = max(1, (int) ($venta->frecuencia_cuota_inicial_meses ?? 1));

            $cuotasSaldo = max($plazo, 0);

            $numPagosSaldo = $frecuencia > 1
                ? (int) ceil($cuotasSaldo / $frecuencia)
                : $cuotasSaldo;

            if ($saldoCuotaInicial > 0 && $numPagosSaldo > 0) {
                $cuotaBase = (int) floor($saldoCuotaInicial / $numPagosSaldo);
                $residuo = $saldoCuotaInicial - ($cuotaBase * $numPagosSaldo);
                $saldo = $saldoCuotaInicial;

                for ($i = 1; $i <= $numPagosSaldo; $i++) {
                    $valorCuota = $cuotaBase;

                    if ($i === $numPagosSaldo) {
                        $valorCuota += $residuo;
                    }

                    $saldo = max($saldo - $valorCuota, 0);

                    $mesOffset = $valorSeparacion > 0
                        ? $i * $frecuencia
                        : ($i - 1) * $frecuencia;

                    $this->crearCuota(
                        $plan,
                        $numeroCuota,
                        $fechaInicio->copy()->addMonths($mesOffset),
                        'Cuota inicial',
                        $valorCuota,
                        $saldo
                    );

                    $numeroCuota++;
                }
            }

            if ($valorRestante > 0) {
                $this->crearCuota(
                    $plan,
                    $numeroCuota,
                    $fechaInicio->copy()->addMonths(max($plazo + 1, 1)),
                    'Valor restante',
                    $valorRestante,
                    0
                );
            }

            return;
        }

        if (($planData['tipo_plan'] ?? null) === 'cuota_inicial_contado') {
            if ($saldoCuotaInicial > 0) {
                $this->crearCuota(
                    $plan,
                    $numeroCuota,
                    $fechaInicio,
                    'Saldo cuota inicial contado',
                    $saldoCuotaInicial,
                    0
                );

                $numeroCuota++;
            }

            if ($valorRestante > 0) {
                $this->crearCuota(
                    $plan,
                    $numeroCuota,
                    $fechaInicio->copy()->addMonth(),
                    'Valor restante',
                    $valorRestante,
                    0
                );
            }

            return;
        }

        if (($planData['tipo_plan'] ?? null) === 'pago_total_diferido') {
            if ($valorRestante > 0) {
                $dias = (int) ($planData['plazo_pago_total_dias'] ?? 60);

                $this->crearCuota(
                    $plan,
                    $numeroCuota,
                    $fechaInicio->copy()->addDays($dias),
                    'Saldo pago diferido',
                    $valorRestante,
                    0
                );
            }

            return;
        }

        if (($planData['tipo_plan'] ?? null) === 'especial_manual') {
            $cuotasManual = $venta->cuotas_manual_ci ?: [];
            $saldo = $saldoCuotaInicial;

            foreach ($cuotasManual as $cuotaManual) {
                $valor = (float) ($cuotaManual['valor_cuota'] ?? 0);
                $fecha = $cuotaManual['fecha_vencimiento'] ?? null;

                if ($valor <= 0 || !$fecha) {
                    continue;
                }

                $saldo = max($saldo - $valor, 0);

                $this->crearCuota(
                    $plan,
                    $numeroCuota,
                    Carbon::parse($fecha)->startOfDay(),
                    'Cuota inicial manual',
                    $valor,
                    $saldo
                );

                $numeroCuota++;
            }

            if ($valorRestante > 0) {
                $this->crearCuota(
                    $plan,
                    $numeroCuota,
                    $fechaInicio->copy()->addMonths(max((int) ($venta->plazo_cuota_inicial_meses ?? 1), 1)),
                    'Valor restante',
                    $valorRestante,
                    0
                );
            }
        }
    }

    protected function crearCuota(
        PlanAmortizacionVenta $plan,
        int $numero,
        Carbon $fecha,
        string $concepto,
        float $valor,
        float $saldo
    ): PlanAmortizacionCuota {
        return PlanAmortizacionCuota::create([
            'id_plan' => $plan->id_plan,
            'numero_cuota' => $numero,
            'fecha_vencimiento' => $fecha->toDateString(),
            'concepto' => $concepto,
            'valor_cuota' => round($valor),
            'valor_interes' => 0,
            'valor_capital' => round($valor),
            'saldo' => round(max($saldo, 0)),
            'estado' => 'Pendiente',
        ]);
    }

    public function regenerarPlanCuotaInicial(Venta $venta): void
    {
        $plan = $venta->planAmortizacion;

        if ($plan) {
            $plan->cuotas()->delete();
            $plan->delete();
        }

        $venta->refresh();
        $this->generarPlanCuotaInicial($venta);
    }

    protected function validarVentaConPlan(array $data, Proyecto $proyecto, array $plan, array $resumen): void
    {
        if ($this->esPlanCuotaInicialMensual($plan)) {
            $plazo = (int) ($data['plazo_cuota_inicial_meses'] ?? 0);

            if ($plazo < 1) {
                throw ValidationException::withMessages([
                    'plazo_cuota_inicial_meses' => 'Debes seleccionar un plazo para este plan.',
                ]);
            }

            $plazos = $this->calcularPlazosDisponiblesPlan($proyecto, $plan, $data['fecha_venta'] ?? null);

            if (!empty($plazos) && !in_array($plazo, $plazos, true)) {
                throw ValidationException::withMessages([
                    'plazo_cuota_inicial_meses' => 'El plazo seleccionado no es válido para la fecha de venta indicada.',
                ]);
            }
        }

        if (($plan['tipo_plan'] ?? null) === 'especial_manual') {
            if (!$this->empleadoAutenticadoPuedeUsarPlanEspecialManual()) {
                throw ValidationException::withMessages([
                    'id_plan_pago_proyecto' => 'El plan especial manual solo está habilitado para Directoras Comerciales.',
                ]);
            }

            $plazo = (int) ($data['plazo_cuota_inicial_meses'] ?? 0);

            if ($plazo < 1) {
                throw ValidationException::withMessages([
                    'plazo_cuota_inicial_meses' => 'Debes seleccionar el plazo de cuota inicial para el plan especial.',
                ]);
            }

            $plazoMaximoProyecto = (int) ($proyecto->plazo_cuota_inicial_meses ?? 0);

            if ($plazoMaximoProyecto > 0 && $plazo > $plazoMaximoProyecto) {
                throw ValidationException::withMessages([
                    'plazo_cuota_inicial_meses' => "El plazo del plan especial no puede superar {$plazoMaximoProyecto} meses, que es el plazo de financiación configurado en el proyecto.",
                ]);
            }

            $cuotasManual = $data['cuotas_manual_ci'] ?? [];

            if (!is_array($cuotasManual) || empty($cuotasManual)) {
                throw ValidationException::withMessages([
                    'cuotas_manual_ci' => 'Debes registrar las cuotas mensuales del plan especial.',
                ]);
            }

            if (count($cuotasManual) !== $plazo) {
                throw ValidationException::withMessages([
                    'cuotas_manual_ci' => 'La cantidad de cuotas debe coincidir con el plazo seleccionado.',
                ]);
            }

            foreach ($cuotasManual as $index => $cuotaManual) {
                if (empty($cuotaManual['fecha_vencimiento'])) {
                    throw ValidationException::withMessages([
                        "cuotas_manual_ci.$index.fecha_vencimiento" => 'La cuota no tiene mes asignado.',
                    ]);
                }

                if (!isset($cuotaManual['valor_cuota']) || (float) $cuotaManual['valor_cuota'] <= 0) {
                    throw ValidationException::withMessages([
                        "cuotas_manual_ci.$index.valor_cuota" => 'El valor de la cuota debe ser mayor a cero.',
                    ]);
                }
            }

            $totalManual = collect($cuotasManual)->sum(fn($c) => (float) ($c['valor_cuota'] ?? 0));
            $saldoCI = (float) ($resumen['saldo_cuota_inicial'] ?? 0);

            if (round($totalManual) !== round($saldoCI)) {
                throw ValidationException::withMessages([
                    'cuotas_manual_ci' => 'La suma de cuotas manuales debe ser igual al saldo de cuota inicial.',
                ]);
            }
        }
    }

    protected function validarSeparacionConPlan(array $data, Proyecto $proyecto, array $plan, array $resumen): void
    {
        $valorSep = (float) ($resumen['valor_separacion'] ?? 0);
        $fechaLimite = $data['fecha_limite_separacion'] ?? null;

        if ($valorSep <= 0) {
            throw ValidationException::withMessages([
                'valor_separacion' => 'El plan seleccionado no tiene valor de separación configurado.',
            ]);
        }

        if (!$fechaLimite) {
            throw ValidationException::withMessages([
                'fecha_limite_separacion' => 'La fecha límite de separación es obligatoria.',
            ]);
        }

        $maxDias = (int) ($proyecto->plazo_max_separacion_dias ?? 0);
        $base = !empty($data['fecha_venta'])
            ? Carbon::parse($data['fecha_venta'])->startOfDay()
            : now()->startOfDay();

        $fechaMax = $base->copy()->addDays($maxDias)->toDateString();

        if ($fechaLimite > $fechaMax) {
            throw ValidationException::withMessages([
                'fecha_limite_separacion' => "La fecha máxima permitida es {$fechaMax}.",
            ]);
        }
    }

    public function calcularPlazosDisponiblesPlan(Proyecto $proyecto, array $plan, ?string $fechaVenta = null): array
    {
        if (!$this->esPlanCuotaInicialMensual($plan)) {
            return [];
        }

        $plazoTotal = (int) ($plan['plazo_cuota_inicial_meses'] ?? 0);

        if ($plazoTotal <= 0) {
            return [];
        }

        if (!$proyecto->fecha_inicio) {
            return range(1, $plazoTotal);
        }

        $inicio = Carbon::parse($proyecto->fecha_inicio)->startOfDay();
        $referencia = $fechaVenta
            ? Carbon::parse($fechaVenta)->startOfDay()
            : now()->startOfDay();

        if ($referencia->lt($inicio)) {
            $mesesTranscurridos = 0;
        } else {
            $mesesTranscurridos = (($referencia->year - $inicio->year) * 12) + ($referencia->month - $inicio->month);

            if ($referencia->day < $inicio->day) {
                $mesesTranscurridos--;
            }

            $mesesTranscurridos = max(0, $mesesTranscurridos);
        }

        $restantes = max($plazoTotal - $mesesTranscurridos, 0);

        return $restantes > 0 ? range(1, $restantes) : [];
    }

    protected function resolverPlazoVenta(array $data, array $plan): ?int
    {
        if ($this->esPlanCuotaInicialMensual($plan)) {
            return (int) ($data['plazo_cuota_inicial_meses'] ?? $plan['plazo_cuota_inicial_meses'] ?? 0);
        }

        if (($plan['tipo_plan'] ?? null) === 'especial_manual') {
            return (int) ($data['plazo_cuota_inicial_meses'] ?? 0);
        }

        return null;
    }

    protected function resolverFrecuenciaVenta(array $data, array $plan): ?int
    {
        if ($this->esPlanCuotaInicialMensual($plan)) {
            return max(1, (int) ($data['frecuencia_cuota_inicial_meses'] ?? $plan['frecuencia_cuota_inicial_meses'] ?? 1));
        }
        if (($plan['tipo_plan'] ?? null) === 'especial_manual') {
            return null;
        }

        return null;
    }

    protected function resolverInmuebleConBloqueo(array $data): array
    {
        if ($data['inmueble_tipo'] === 'apartamento') {
            $inmueble = Apartamento::where('id_apartamento', $data['inmueble_id'])
                ->lockForUpdate()
                ->firstOrFail();
        } else {
            $inmueble = Local::where('id_local', $data['inmueble_id'])
                ->lockForUpdate()
                ->firstOrFail();
        }

        $estadoDisponibleId = EstadoInmueble::whereRaw('LOWER(nombre) = ?', ['disponible'])
            ->value('id_estado_inmueble');

        if ($inmueble->id_estado_inmueble !== $estadoDisponibleId) {
            throw new \RuntimeException('El inmueble ya no está disponible.');
        }

        $valorBase = (float) ($inmueble->valor_final ?? $inmueble->valor_total ?? 0);

        return [$inmueble, $valorBase];
    }

    protected function resolverParqueaderoAdicional(array $data, Proyecto $proyecto): ?Parqueadero
    {
        $idParqueadero = $data['id_parqueadero'] ?? null;

        if (empty($idParqueadero)) {
            return null;
        }

        if ($data['inmueble_tipo'] !== 'apartamento') {
            throw new \RuntimeException('El parqueadero adicional solo aplica para apartamentos.');
        }

        $parqueadero = Parqueadero::where('id_parqueadero', $idParqueadero)
            ->lockForUpdate()
            ->firstOrFail();

        if (!empty($parqueadero->id_apartamento)) {
            throw new \RuntimeException('El parqueadero seleccionado no es adicional.');
        }

        if (!empty($parqueadero->id_proyecto) && (int) $parqueadero->id_proyecto !== (int) $proyecto->id_proyecto) {
            throw new \RuntimeException('El parqueadero no pertenece al proyecto seleccionado.');
        }

        $ocupado = Venta::whereNotNull('id_parqueadero')
            ->where('id_parqueadero', $idParqueadero)
            ->whereIn('tipo_operacion', ['venta', 'separacion'])
            ->exists();

        if ($ocupado) {
            throw new \RuntimeException('El parqueadero ya fue reservado en otra operación.');
        }

        return $parqueadero;
    }

    protected function mapearInmuebleEnData(array &$data): void
    {
        if ($data['inmueble_tipo'] === 'apartamento') {
            $data['id_apartamento'] = $data['inmueble_id'];
            $data['id_local'] = null;
        } else {
            $data['id_local'] = $data['inmueble_id'];
            $data['id_apartamento'] = null;
            $data['id_parqueadero'] = null;
        }

        unset($data['inmueble_tipo'], $data['inmueble_id']);
    }

    protected function validarOperacionDuplicada(array $data): void
    {
        $existeOperacion = Venta::whereIn('tipo_operacion', ['venta', 'separacion'])
            ->when($data['id_apartamento'] ?? null, fn($q) => $q->where('id_apartamento', $data['id_apartamento']))
            ->when($data['id_local'] ?? null, fn($q) => $q->where('id_local', $data['id_local']))
            ->exists();

        if ($existeOperacion) {
            throw new \RuntimeException('Ya existe una operación para este inmueble.');
        }
    }

    protected function normalizarPlanReal(PlanPagoProyecto $plan): array
    {
        return [
            'id_plan_pago_proyecto' => $plan->id_plan_pago_proyecto,
            'codigo' => $plan->codigo,
            'nombre' => $plan->nombre,
            'descripcion' => $plan->descripcion,
            'orden' => $plan->orden,
            'tipo_plan' => $plan->tipo_plan,
            'valor_separacion' => (float) $plan->valor_separacion,
            'porcentaje_cuota_inicial' => $plan->porcentaje_cuota_inicial !== null ? (float) $plan->porcentaje_cuota_inicial : null,
            'plazo_cuota_inicial_meses' => $plan->plazo_cuota_inicial_meses !== null ? (int) $plan->plazo_cuota_inicial_meses : null,
            'frecuencia_cuota_inicial_meses' => $plan->frecuencia_cuota_inicial_meses !== null ? (int) $plan->frecuencia_cuota_inicial_meses : 1,
            'plazo_pago_total_dias' => $plan->plazo_pago_total_dias !== null ? (int) $plan->plazo_pago_total_dias : null,
            'porcentaje_escritura' => (float) ($plan->porcentaje_escritura ?? 0),
            'tipo_descuento' => $plan->tipo_descuento ?: 'ninguno',
            'valor_descuento' => $plan->valor_descuento !== null ? (float) $plan->valor_descuento : null,
            'base_descuento' => $plan->base_descuento ?: 'ninguna',
            'beneficio_comercial' => $plan->beneficio_comercial,
            'permite_plazo_manual' => (bool) $plan->permite_plazo_manual,
            'permite_cuotas_manuales' => (bool) $plan->permite_cuotas_manuales,
            'activo' => (bool) $plan->activo,
            'es_plan_default_proyecto' => false,
        ];
    }

    protected function planDefaultProyecto(Proyecto $proyecto): array
    {
        $porcentajeCuotaInicial = (float) ($proyecto->porcentaje_cuota_inicial_min ?? 0);

        return [
            'id_plan_pago_proyecto' => null,
            'codigo' => 'COND-PROYECTO',
            'nombre' => 'Condiciones económicas del proyecto',
            'descripcion' => 'Plan generado automáticamente con los parámetros económicos generales del proyecto.',
            'orden' => 1,
            'tipo_plan' => 'condiciones_proyecto',
            'valor_separacion' => (float) ($proyecto->valor_min_separacion ?? 0),
            'porcentaje_cuota_inicial' => $porcentajeCuotaInicial,
            'plazo_cuota_inicial_meses' => (int) ($proyecto->plazo_cuota_inicial_meses ?? 0),
            'frecuencia_cuota_inicial_meses' => 1,
            'plazo_pago_total_dias' => null,
            'porcentaje_escritura' => max(100 - $porcentajeCuotaInicial, 0),
            'tipo_descuento' => 'ninguno',
            'valor_descuento' => null,
            'base_descuento' => 'ninguna',
            'beneficio_comercial' => null,
            'permite_plazo_manual' => false,
            'permite_cuotas_manuales' => false,
            'activo' => true,
            'es_plan_default_proyecto' => true,
        ];
    }

    protected function esPlanCuotaInicialMensual(array $plan): bool
    {
        return in_array($plan['tipo_plan'] ?? null, ['cuota_inicial_mensual', 'condiciones_proyecto'], true);
    }

    public function asignarParqueaderoAApartamento(?int $idParqueadero, ?int $idApartamento): void
    {
        if (!$idParqueadero || !$idApartamento) {
            return;
        }

        Parqueadero::where('id_parqueadero', $idParqueadero)
            ->whereNull('id_apartamento')
            ->update(['id_apartamento' => $idApartamento]);
    }

    public function liberarParqueaderoDeApartamento(?int $idParqueadero, ?int $idApartamento): void
    {
        if (!$idParqueadero) {
            return;
        }

        Parqueadero::where('id_parqueadero', $idParqueadero)
            ->when($idApartamento, fn($q) => $q->where('id_apartamento', $idApartamento))
            ->update(['id_apartamento' => null]);
    }

    protected function empleadoAutenticadoPuedeUsarPlanEspecialManual(): bool
    {
        $empleado = request()->user()->load('cargo');

        if (!$empleado) {
            return false;
        }

        $empleado->loadMissing('cargo');

        $cargo = strtolower(
            trim(
                str_replace(
                    ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'],
                    ['a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u'],
                    $empleado->cargo->nombre ?? ''
                )
            )
        );

        return $cargo === 'directora comercial';
    }
}
