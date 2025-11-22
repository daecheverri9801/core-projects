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
            $this->pricingService->recalcPreciosProyecto($proyecto->id_proyecto);

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

    protected function validarVenta(array $data, Proyecto $proyecto): void
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
        $plazo = $venta->plazo_cuota_inicial_meses ?: 0;
        $monto = (float)($venta->cuota_inicial ?? 0);

        if ($plazo <= 0 || $monto <= 0) {
            return;
        }

        $fechaInicio = $venta->fecha_venta ?? now();

        $plan = PlanAmortizacionVenta::create([
            'id_venta' => $venta->id_venta,
            'tipo_plan' => 'cuota_inicial',
            'valor_interes_anual' => 0,
            'plazo_meses' => $plazo,
            'fecha_inicio' => $fechaInicio,
            'observacion' => 'Plan de amortización de cuota inicial',
        ]);

        $cuotaBase = floor($monto / $plazo);
        $residuo = $monto - ($cuotaBase * $plazo);

        for ($i = 1; $i <= $plazo; $i++) {
            $valorCuota = $cuotaBase;

            // La última cuota absorbe diferencia residual
            if ($i === $plazo) {
                $valorCuota += $residuo;
            }

            $saldo = $monto - ($cuotaBase * ($i - 1)) - $valorCuota;
            $saldo = max($saldo, 0);

            PlanAmortizacionCuota::create([
                'id_plan' => $plan->id_plan,
                'numero_cuota' => $i,
                'fecha_cuota' => Carbon::parse($fechaInicio)->addMonths($i - 1),
                'valor_cuota' => $valorCuota,
                'saldo' => $saldo,
                'estado' => 'Pendiente',
            ]);
        }
    }
}
