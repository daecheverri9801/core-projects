<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\Apartamento;
use App\Models\Local;
use App\Models\Proyecto;
use App\Models\EstadoInmueble;
use App\Models\Parqueadero;
use App\Models\PlanAmortizacionVenta;
use App\Models\PlanAmortizacionCuota;
use App\Services\ProyectoPricingService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VentaService
{
    public function __construct(
        protected ProyectoPricingService $pricingService
    ) {}

    public function crearOperacion(array $data): Venta
    {
        return DB::transaction(function () use ($data) {

            /** @var Proyecto $proyecto */
            $proyecto = Proyecto::findOrFail($data['id_proyecto']);

            // 1) Resolver inmueble con lock
            if ($data['inmueble_tipo'] === 'apartamento') {
                $queryInmueble = Apartamento::where('id_apartamento', $data['inmueble_id'])->lockForUpdate();
            } else {
                $queryInmueble = Local::where('id_local', $data['inmueble_id'])->lockForUpdate();
            }

            $inmueble = $queryInmueble->firstOrFail();

            // 2) Verificar disponible
            $estadoDisponibleId = EstadoInmueble::where('nombre', 'Disponible')->value('id_estado_inmueble');
            if ($inmueble->id_estado_inmueble !== $estadoDisponibleId) {
                throw new \RuntimeException('El inmueble ya no está disponible.');
            }

            // 3) Parqueadero adicional (opcional) - validar y reservar
            $idParqueadero = $data['id_parqueadero'] ?? null;
            $parqueadero = null;

            if (!empty($idParqueadero)) {
                if ($data['inmueble_tipo'] !== 'apartamento') {
                    throw new \RuntimeException('El parqueadero adicional solo aplica para apartamentos.');
                }

                $parqueadero = Parqueadero::where('id_parqueadero', $idParqueadero)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Debe ser adicional
                if (!empty($parqueadero->id_apartamento)) {
                    throw new \RuntimeException('El parqueadero seleccionado no es adicional (está asignado a un apartamento).');
                }

                // Debe pertenecer al proyecto
                if (!empty($parqueadero->id_proyecto) && (int)$parqueadero->id_proyecto !== (int)$proyecto->id_proyecto) {
                    throw new \RuntimeException('El parqueadero no pertenece al proyecto seleccionado.');
                }

                // Debe estar libre (no asignado a otra venta/separación)
                $ocupado = Venta::whereNotNull('id_parqueadero')
                    ->where('id_parqueadero', $idParqueadero)
                    ->whereIn('tipo_operacion', ['venta', 'separacion'])
                    ->exists();

                if ($ocupado) {
                    throw new \RuntimeException('El parqueadero ya fue reservado en otra operación.');
                }
            }

            // 4) Calcular valores (backend manda)
            $valorBaseInmueble = (float)($inmueble->valor_final ?? $inmueble->valor_total ?? 0);
            $precioParqueadero = $parqueadero ? (float)($parqueadero->precio ?? 0) : 0.0;

            $data['valor_base'] = $valorBaseInmueble;
            $data['valor_total'] = $valorBaseInmueble + $precioParqueadero;

            // Recalcular restante si es venta
            if (($data['tipo_operacion'] ?? null) === 'venta') {
                $cuotaInicial = (float)($data['cuota_inicial'] ?? 0);
                $data['valor_restante'] = max(0, $data['valor_total'] - $cuotaInicial);
            }

            // 5) Validaciones de negocio
            if ($data['tipo_operacion'] === 'venta') {
                $this->validarVenta($data, $proyecto);
            } elseif ($data['tipo_operacion'] === 'separacion') {
                $this->validarSeparacion($data, $proyecto);
            }

            // 6) Mapear IDs inmueble
            if ($data['inmueble_tipo'] === 'apartamento') {
                $data['id_apartamento'] = $data['inmueble_id'];
                $data['id_local'] = null;
            } else {
                $data['id_local'] = $data['inmueble_id'];
                $data['id_apartamento'] = null;
                // por seguridad, no permitir parqueadero en local
                $data['id_parqueadero'] = null;
            }

            unset($data['inmueble_tipo'], $data['inmueble_id']);

            // 7) Evitar duplicidad por inmueble
            $existeOperacion = Venta::whereIn('tipo_operacion', ['venta', 'separacion'])
                ->when($data['id_apartamento'] ?? null, fn($q) => $q->where('id_apartamento', $data['id_apartamento']))
                ->when($data['id_local'] ?? null, fn($q) => $q->where('id_local', $data['id_local']))
                ->exists();

            if ($existeOperacion) {
                throw new \RuntimeException('Ya existe una operación para este inmueble.');
            }

            // 8) Crear operación
            /** @var Venta $venta */
            $venta = Venta::create($data);

            // ✅ Si hay parqueadero adicional y es apartamento, asignarlo al apartamento
            $this->asignarParqueaderoAApartamento(
                $venta->id_parqueadero ? (int)$venta->id_parqueadero : null,
                $venta->id_apartamento ? (int)$venta->id_apartamento : null
            );

            // 9) Actualizar estado del inmueble
            $estadoDestino = $data['tipo_operacion'] === 'venta' ? 'Vendido' : 'Separado';
            $idEstadoDestino = EstadoInmueble::where('nombre', $estadoDestino)->value('id_estado_inmueble');
            $inmueble->update(['id_estado_inmueble' => $idEstadoDestino]);

            // 10) Si es venta → plan cuota inicial
            if ($data['tipo_operacion'] === 'venta') {
                $this->generarPlanCuotaInicial($venta, $proyecto);
            }

            // 11) Recalcular precios de proyecto
            app(\App\Services\PriceEngine::class)->recalcularProyecto($proyecto);

            session([
                'debug_venta' => [
                    'tipo' => $data['tipo_operacion'],
                    'valor_base' => $data['valor_base'] ?? null,
                    'parqueadero' => $precioParqueadero,
                    'valor_total' => $data['valor_total'] ?? null,
                    'cuota_inicial' => $data['cuota_inicial'] ?? null,
                    'estado_inmueble' => $estadoDestino ?? null,
                ]
            ]);

            return $venta;
        });
    }

    public function validarVenta(array $data, Proyecto $proyecto): void
    {
        $valorTotal = (float)($data['valor_total'] ?? 0);
        $cuotaInicial = (float)($data['cuota_inicial'] ?? 0);
        $plazoMeses = (int)($data['plazo_cuota_inicial_meses'] ?? 0);

        $minCuota = $valorTotal * ($proyecto->porcentaje_cuota_inicial_min / 100);
        if ($cuotaInicial < $minCuota) {
            throw new \RuntimeException('La cuota inicial es menor al mínimo permitido para el proyecto.');
        }

        if (
            $proyecto->plazo_max_cuota_inicial_meses > 0 &&
            $plazoMeses > $proyecto->plazo_max_cuota_inicial_meses
        ) {
            throw new \RuntimeException('El plazo de cuota inicial excede el máximo permitido para el proyecto.');
        }

        $frecuencia = (int)($data['frecuencia_cuota_inicial_meses'] ?? 1);
        if ($frecuencia < 1) $frecuencia = 1;

        if ($plazoMeses > 0 && $frecuencia > $plazoMeses) {
            throw new \RuntimeException('La frecuencia no puede ser mayor al plazo de cuota inicial.');
        }
    }

    protected function validarSeparacion(array $data, Proyecto $proyecto): void
    {
        $valorSep = (float)($data['valor_separacion'] ?? 0);
        $fechaLimite = $data['fecha_limite_separacion'] ?? null;

        if ($valorSep < $proyecto->valor_min_separacion) {
            throw new \Illuminate\Validation\ValidationException(
                validator: validator([], []),
                response: null,
                errorBag: 'default',
                errors: ['valor_separacion' => ['El valor de separación es menor al mínimo permitido.']]
            );
        }

        if ($fechaLimite) {
            $maxDias = (int)$proyecto->plazo_max_separacion_dias;
            $fechaMax = now()->addDays($maxDias)->toDateString();

            if ($fechaLimite > $fechaMax) {
                throw new \Illuminate\Validation\ValidationException(
                    validator: validator([], []),
                    response: null,
                    errorBag: 'default',
                    errors: ['fecha_limite_separacion' => ['La fecha excede el máximo permitido por el proyecto.']]
                );
            }
        }
    }

    protected function generarPlanCuotaInicial(Venta $venta, Proyecto $proyecto): void
    {
        $plazo = (int)($venta->plazo_cuota_inicial_meses ?? 0);
        $monto = (float)($venta->cuota_inicial ?? 0);
        $frecuencia = (int)($venta->frecuencia_cuota_inicial_meses ?? 1);

        if ($plazo <= 0 || $monto <= 0) return;
        if ($frecuencia < 1) $frecuencia = 1;
        if ($frecuencia > $plazo) $frecuencia = $plazo;

        $fechaInicio = $venta->fecha_venta ?? now();

        $numPagos = (int) ceil($plazo / $frecuencia);
        if ($numPagos < 1) $numPagos = 1;

        $plan = PlanAmortizacionVenta::create([
            'id_venta' => $venta->id_venta,
            'tipo_plan' => 'cuota_inicial',
            'valor_interes_anual' => 0,
            'plazo_meses' => $plazo,
            'fecha_inicio' => $fechaInicio,
            'observacion' => "Plan cuota inicial (cada {$frecuencia} mes(es))",
        ]);

        $cuotaBase = (int) floor($monto / $numPagos);
        $residuo = $monto - ($cuotaBase * $numPagos);

        for ($i = 1; $i <= $numPagos; $i++) {
            $valorCuota = $cuotaBase;

            if ($i === $numPagos) {
                $valorCuota += $residuo;
            }

            $pagadoHastaAhora = ($cuotaBase * ($i - 1));
            $saldo = $monto - $pagadoHastaAhora - $valorCuota;
            $saldo = max($saldo, 0);

            $mesOffset = ($i - 1) * $frecuencia;

            PlanAmortizacionCuota::create([
                'id_plan' => $plan->id_plan,
                'numero_cuota' => $i,
                'fecha_vencimiento' => Carbon::parse($fechaInicio)->addMonths($mesOffset),
                'valor_cuota' => $valorCuota,
                'valor_interes' => 0,
                'valor_capital' => $valorCuota,
                'saldo' => $saldo,
                'estado' => 'Pendiente',
            ]);
        }
    }

    public function regenerarPlanCuotaInicial(Venta $venta): void
    {
        $proyecto = Proyecto::find($venta->id_proyecto);
        if (!$proyecto) return;

        $plan = $venta->planAmortizacion;
        if ($plan) {
            $plan->cuotas()->delete();
            $plan->delete();
        }

        $this->generarPlanCuotaInicial($venta, $proyecto);
    }

    public function asignarParqueaderoAApartamento(?int $idParqueadero, ?int $idApartamento): void
    {
        if (!$idParqueadero || !$idApartamento) return;

        Parqueadero::where('id_parqueadero', $idParqueadero)
            ->whereNull('id_apartamento')
            ->update(['id_apartamento' => $idApartamento]);
    }

    public function liberarParqueaderoDeApartamento(?int $idParqueadero, ?int $idApartamento): void
    {
        if (!$idParqueadero) return;

        Parqueadero::where('id_parqueadero', $idParqueadero)
            ->when($idApartamento, fn($q) => $q->where('id_apartamento', $idApartamento))
            ->update(['id_apartamento' => null]);
    }
}
