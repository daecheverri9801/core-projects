<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\Apartamento;
use App\Models\Local;
use App\Models\Proyecto;
use App\Models\EstadoInmueble;
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

            // 1. Cargar proyecto
            /** @var \App\Models\Proyecto $proyecto */
            $proyecto = Proyecto::findOrFail($data['id_proyecto']);

            // 2. Resolver inmueble
            if ($data['inmueble_tipo'] === 'apartamento') {
                $queryInmueble = Apartamento::where('id_apartamento', $data['inmueble_id'])
                    ->lockForUpdate();
            } else {
                $queryInmueble = Local::where('id_local', $data['inmueble_id'])
                    ->lockForUpdate();
            }

            $inmueble = $queryInmueble->firstOrFail();

            // 3. Verificar que siga disponible
            $estadoDisponibleId = EstadoInmueble::where('nombre', 'Disponible')
                ->value('id_estado_inmueble');

            if ($inmueble->id_estado_inmueble !== $estadoDisponibleId) {
                throw new \RuntimeException('El inmueble ya no está disponible.');
            }

            // 4. Validaciones por tipo de operación
            if ($data['tipo_operacion'] === 'venta') {
                $this->validarVenta($data, $proyecto);
            } elseif ($data['tipo_operacion'] === 'separacion') {
                $this->validarSeparacion($data, $proyecto);
            }

            // 5. Mapear IDs de inmueble
            if ($data['inmueble_tipo'] === 'apartamento') {
                $data['id_apartamento'] = $data['inmueble_id'];
                $data['id_local'] = null;
            } else {
                $data['id_local'] = $data['inmueble_id'];
                $data['id_apartamento'] = null;
            }

            unset($data['inmueble_tipo'], $data['inmueble_id']);

            $existeOperacion = Venta::whereIn('tipo_operacion', ['venta', 'separacion'])
                ->when($data['id_apartamento'] ?? null, fn($q) => $q->where('id_apartamento', $data['id_apartamento']))
                ->when($data['id_local'] ?? null, fn($q) => $q->where('id_local', $data['id_local']))
                ->exists();

            if ($existeOperacion) {
                throw new \RuntimeException('Ya existe una operación para este inmueble.');
            }

            // 6. Crear venta/separación
            /** @var \App\Models\Venta $venta */
            $venta = Venta::create($data);

            // 7. Actualizar estado del inmueble
            $estadoDestino = $data['tipo_operacion'] === 'venta'
                ? 'Vendido'
                : 'Separado';

            $idEstadoDestino = EstadoInmueble::where('nombre', $estadoDestino)
                ->value('id_estado_inmueble');

            $inmueble->update(['id_estado_inmueble' => $idEstadoDestino]);

            // 8. Si es venta → generar plan de amortización
            if ($data['tipo_operacion'] === 'venta') {
                $this->generarPlanCuotaInicial($venta, $proyecto);
            }

            // 9. Recalcular precios de inmuebles disponibles del proyecto según políticas
            app(\App\Services\PriceEngine::class)->recalcularProyecto($proyecto);

            session([
                'debug_venta' => [
                    'tipo' => $data['tipo_operacion'],
                    'valor_total' => $data['valor_total'] ?? null,
                    'cuota_inicial' => $data['cuota_inicial'] ?? null,
                    'valor_final' => $inmueble->valor_final ?? null,
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

        // Validar valor
        if ($valorSep < $proyecto->valor_min_separacion) {
            throw new \Illuminate\Validation\ValidationException(
                validator: validator([], []),
                response: null,
                errorBag: 'default',
                errors: ['valor_separacion' => ['El valor de separación es menor al mínimo permitido.']]
            );
        }

        // Validar fecha límite
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

        // número de pagos según frecuencia (ej: 12 meses cada 3 => 4 pagos)
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

            // última cuota absorbe residuo
            if ($i === $numPagos) {
                $valorCuota += $residuo;
            }

            $pagadoHastaAhora = ($cuotaBase * ($i - 1));
            $saldo = $monto - $pagadoHastaAhora - $valorCuota;
            $saldo = max($saldo, 0);

            // vencimientos: 0, frecuencia, 2*frecuencia, ...
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
}
