<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Operación</title>

    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            color: #1f2937;
        }

        .container {
            max-width: 680px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .header {
            background: linear-gradient(135deg, #FFEA00 0%, #D1C000 100%);
            padding: 30px 20px;
            text-align: center;
        }

        .header-logo {
            max-height: 70px;
            width: auto;
            display: block;
            margin: 0 auto 15px auto;
        }

        .header h1 {
            color: #1A1700;
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }

        .header p {
            color: #474100;
            margin: 5px 0 0;
            font-size: 16px;
        }

        .content {
            padding: 30px;
        }

        .intro {
            margin-bottom: 24px;
            font-size: 14px;
            line-height: 1.6;
            color: #374151;
        }

        .section {
            margin-bottom: 25px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }

        .section-title {
            background-color: #1e3a5f;
            color: white;
            padding: 11px 15px;
            margin: 0;
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 0.2px;
        }

        .section-content {
            padding: 15px;
            background: #fafafa;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .table td,
        .table th {
            padding: 8px 10px;
            border-bottom: 1px solid #eeeeee;
            font-size: 13px;
            vertical-align: top;
        }

        .table th {
            background-color: #1e3a5f;
            color: white;
            text-align: left;
            font-weight: bold;
        }

        .table td:first-child {
            font-weight: bold;
            width: 42%;
            background-color: #f5f5f5;
            color: #374151;
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        .total-row td {
            border-top: 2px solid #1e3a5f;
            font-weight: bold;
            background-color: #f8fafc;
        }

        .badge {
            display: inline-block;
            padding: 4px 9px;
            border-radius: 999px;
            background-color: #FFFDE6;
            color: #474100;
            font-size: 12px;
            font-weight: bold;
            border: 1px solid #D1C000;
        }

        .highlight-box {
            background-color: #FFFDE6;
            border: 1px solid #D1C000;
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #474100;
        }

        .small-text {
            font-size: 12px;
            color: #6b7280;
            line-height: 1.5;
        }

        .footer {
            background-color: #f8f8f8;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
        }
    </style>
</head>

<body>
    @php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    $esEmpleado = ($tipo ?? null) === 'empleado';
    $esCliente = ($tipo ?? null) === 'cliente';
    $esAdministrativo = ($tipo ?? null) === 'administrativo';

    $esApartamento = !is_null($venta->apartamento);
    $esVenta = $venta->tipo_operacion === 'venta';
    $esSeparacion = $venta->tipo_operacion === 'separacion';

    $tituloOperacion = $esVenta ? 'VENTA' : 'SEPARACIÓN';
    $tipoInmueble = $esApartamento ? 'Apartamento' : 'Local';

    $planSnapshotRaw = $venta->plan_pago_snapshot ?? null;

    if (is_string($planSnapshotRaw)) {
    $planSnapshot = json_decode($planSnapshotRaw, true) ?: [];
    } elseif (is_array($planSnapshotRaw)) {
    $planSnapshot = $planSnapshotRaw;
    } elseif (is_object($planSnapshotRaw)) {
    $planSnapshot = json_decode(json_encode($planSnapshotRaw), true) ?: [];
    } else {
    $planSnapshot = [];
    }

    $tipoPlanLabels = [
    'cuota_inicial_mensual' => 'Cuota inicial mensual',
    'cuota_inicial_contado' => 'Cuota inicial de contado',
    'pago_total_diferido' => 'Pago total diferido',
    'especial_manual' => 'Plan especial manual',
    'condiciones_proyecto' => 'Condiciones económicas del proyecto',
    ];

    $planTipo = $venta->plan_pago_tipo ?? ($planSnapshot['tipo_plan'] ?? null);
    $planTipoLabel = $tipoPlanLabels[$planTipo] ?? ($planTipo ?: '—');

    $valorBase = (float) ($venta->valor_base ?? 0);
    $valorParqueadero = (float) optional($venta->parqueadero)->precio;
    $valorTotalSinDescuento = (float) (
    $venta->valor_total_sin_descuento
    ?? (($valorBase + $valorParqueadero) > 0 ? ($valorBase + $valorParqueadero) : $venta->valor_total)
    ?? 0
    );

    $valorDescuento = (float) ($venta->valor_descuento ?? 0);
    $valorTotal = (float) ($venta->valor_total ?? 0);

    $valorSeparacion = (float) (
    $venta->valor_separacion
    ?? optional($venta->proyecto)->valor_min_separacion
    ?? 0
    );

    $cuotaInicial = (float) ($venta->cuota_inicial ?? 0);
    $saldoCuotaInicial = (float) (
    $venta->saldo_cuota_inicial
    ?? max($cuotaInicial - $valorSeparacion, 0)
    );

    $valorRestante = (float) (
    $venta->valor_restante
    ?? max($valorTotal - $cuotaInicial, 0)
    );

    $cuotasPlan = $venta->planAmortizacion && $venta->planAmortizacion->cuotas
    ? $venta->planAmortizacion->cuotas
    : collect();

    $cuotasCuotaInicial = $cuotasPlan->filter(function ($cuota) {
    $concepto = strtolower((string) ($cuota->concepto ?? ''));

    return Str::contains($concepto, [
    'cuota inicial',
    'saldo cuota inicial',
    'cuota inicial manual',
    ]);
    });

    $numeroCuotasCI = $cuotasCuotaInicial->count() > 0
    ? $cuotasCuotaInicial->count()
    : (int) ($venta->plazo_cuota_inicial_meses ?? 0);

    $valorCuotaCI = $cuotasCuotaInicial->count() > 0
    ? (float) ($cuotasCuotaInicial->first()->valor_cuota ?? 0)
    : ($numeroCuotasCI > 0 ? round($saldoCuotaInicial / $numeroCuotasCI) : 0);

    $frecuenciaTexto = 'No aplica';

    if ($esVenta) {
    if ($planTipo === 'especial_manual') {
    $frecuenciaTexto = 'No aplica - cuotas manuales';
    } elseif (!empty($venta->frecuencia_cuota_inicial_meses)) {
    $frecuenciaTexto = 'Cada ' . $venta->frecuencia_cuota_inicial_meses . ' meses';
    }
    }

    $cuotasManualCI = $venta->cuotas_manual_ci;

    if (is_string($cuotasManualCI)) {
    $cuotasManualCI = json_decode($cuotasManualCI, true) ?: [];
    }

    if (!is_array($cuotasManualCI)) {
    $cuotasManualCI = [];
    }

    $textoCuotasManualCI = count($cuotasManualCI) > 0
    ? count($cuotasManualCI) . ' cuota' . (count($cuotasManualCI) === 1 ? '' : 's') . ' manuales'
    : 'No aplica';

    $descuentoConfigurado = 'No aplica';

    if (($planSnapshot['tipo_descuento'] ?? 'ninguno') === 'valor_fijo') {
    $descuentoConfigurado = '$' . number_format((float) ($planSnapshot['valor_descuento'] ?? 0), 0, ',', '.');
    } elseif (($planSnapshot['tipo_descuento'] ?? 'ninguno') === 'porcentaje') {
    $descuentoConfigurado = number_format((float) ($planSnapshot['valor_descuento'] ?? 0), 2, ',', '.') . '%';
    }

    $proyectoZonas = optional($venta->proyecto->zonasSociales ?? collect())
    ->pluck('nombre')
    ->filter()
    ->join(', ');

    $seccion = 1;
    @endphp

    <div class="container">
        <div class="header">
            <img src="{{ asset('images/logo-ayc.png') }}" alt="Constructora A&C" class="header-logo">
            <h1>REPORTE DE OPERACIÓN</h1>
            <p>{{ $tituloOperacion }} · {{ $tipoInmueble }}</p>
        </div>

        <div class="content">
            @if($esEmpleado)
            <div class="intro">
                <p>
                    Estimado(a)
                    <strong>{{ trim(($venta->empleado->nombre ?? '') . ' ' . ($venta->empleado->apellido ?? '')) ?: 'Empleado' }}</strong>,
                </p>
                <p>
                    Se ha registrado una nueva operación en el sistema con los siguientes detalles:
                </p>
            </div>
            @elseif($esCliente)
            <div class="intro">
                <p>
                    Estimado(a) <strong>{{ $venta->cliente->nombre ?? 'Cliente' }}</strong>,
                </p>
                <p>
                    Le informamos que se ha registrado una operación en nuestro sistema con los siguientes detalles:
                </p>
            </div>
            @else
            <div class="intro">
                <p>Estimado(a),</p>
                <p>
                    Se ha registrado una nueva operación en el sistema. A continuación se comparten los detalles para control administrativo:
                </p>
            </div>
            @endif

            <div class="highlight-box">
                <strong>Tipo de operación:</strong> {{ $tituloOperacion }}
                <br>
                <strong>Plan de venta:</strong> {{ $venta->plan_pago_nombre ?? 'Condiciones del proyecto' }}
                <br>
                <strong>Valor total cotizado:</strong> ${{ number_format($valorTotal, 0, ',', '.') }}
            </div>

            <div class="section">
                <div class="section-title">{{ $seccion++ }}. DATOS DEL PROYECTO</div>
                <div class="section-content">
                    <table class="table">
                        <tr>
                            <td>Nombre:</td>
                            <td>{{ $venta->proyecto->nombre ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>Ubicación:</td>
                            <td>{{ $venta->proyecto->ubicacion->direccion ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>Zonas sociales:</td>
                            <td>{{ $proyectoZonas ?: '—' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($esEmpleado || $esAdministrativo)
            <div class="section">
                <div class="section-title">{{ $seccion++ }}. DATOS DEL CLIENTE</div>
                <div class="section-content">
                    <table class="table">
                        <tr>
                            <td>Nombre:</td>
                            <td>{{ $venta->cliente->nombre ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>Documento:</td>
                            <td>{{ $venta->documento_cliente ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>Teléfono:</td>
                            <td>{{ $venta->cliente->telefono ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>Correo:</td>
                            <td>{{ $venta->cliente->correo ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>Dirección:</td>
                            <td>{{ $venta->cliente->direccion ?? '—' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            @endif

            <div class="section">
                <div class="section-title">{{ $seccion++ }}. DATOS DEL ASESOR</div>
                <div class="section-content">
                    <table class="table">
                        <tr>
                            <td>Nombre:</td>
                            <td>{{ trim(($venta->empleado->nombre ?? '') . ' ' . ($venta->empleado->apellido ?? '')) ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td>Correo:</td>
                            <td>{{ $venta->empleado->email ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>Teléfono:</td>
                            <td>{{ $venta->empleado->telefono ?? '—' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="section">
                <div class="section-title">{{ $seccion++ }}. INFORMACIÓN DEL INMUEBLE</div>
                <div class="section-content">
                    <table class="table">
                        <tr>
                            <td>Tipo:</td>
                            <td>{{ $esApartamento ? 'Apartamento' : 'Local Comercial' }}</td>
                        </tr>
                        <tr>
                            <td>Número:</td>
                            <td>{{ $venta->apartamento->numero ?? $venta->local->numero ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>Torre:</td>
                            <td>{{ $venta->apartamento->torre->nombre_torre ?? $venta->local->torre->nombre_torre ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>Piso:</td>
                            <td>{{ $venta->apartamento->pisoTorre->nivel ?? $venta->local->pisoTorre->nivel ?? '—' }}</td>
                        </tr>

                        @if($esApartamento)
                        <tr>
                            <td>Tipo apartamento:</td>
                            <td>{{ $venta->apartamento->tipoApartamento->nombre ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>Habitaciones:</td>
                            <td>{{ $venta->apartamento->tipoApartamento->cantidad_habitaciones ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>Baños:</td>
                            <td>{{ $venta->apartamento->tipoApartamento->cantidad_banos ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>Área construida:</td>
                            <td>{{ $venta->apartamento->tipoApartamento->area_construida ?? '—' }} m²</td>
                        </tr>
                        <tr>
                            <td>Área privada:</td>
                            <td>{{ $venta->apartamento->tipoApartamento->area_privada ?? '—' }} m²</td>
                        </tr>
                        <tr>
                            <td>Parqueadero:</td>
                            <td>
                                {{
                                        $venta->apartamento &&
                                        $venta->apartamento->parqueaderos &&
                                        $venta->apartamento->parqueaderos->count() > 0
                                            ? 'Sí'
                                            : 'No'
                                    }}
                            </td>
                        </tr>
                        <tr>
                            <td>Parqueadero adicional:</td>
                            <td>
                                @if($venta->parqueadero)
                                Sí - {{ $venta->parqueadero->tipo ?? '—' }}
                                {{ $venta->parqueadero->numero ? ' / ' . $venta->parqueadero->numero : '' }}
                                @else
                                No
                                @endif
                            </td>
                        </tr>
                        @else
                        <tr>
                            <td>Área total:</td>
                            <td>{{ $venta->local->area_total_local ?? '—' }} m²</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <div class="section">
                <div class="section-title">{{ $seccion++ }}. PLAN DE VENTA</div>
                <div class="section-content">
                    <table class="table">
                        <tr>
                            <td>Plan de venta:</td>
                            <td>{{ $venta->plan_pago_nombre ?? 'Condiciones del proyecto' }}</td>
                        </tr>
                        <tr>
                            <td>Código del plan:</td>
                            <td>{{ $venta->plan_pago_codigo ?? ($planSnapshot['codigo'] ?? '—') }}</td>
                        </tr>
                        <tr>
                            <td>Tipo de plan:</td>
                            <td>
                                <span class="badge">{{ $planTipoLabel }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>Beneficio / compromiso comercial:</td>
                            <td>{{ $planSnapshot['beneficio_comercial'] ?? 'No aplica' }}</td>
                        </tr>
                        <tr>
                            <td>Tipo de descuento:</td>
                            <td>{{ $planSnapshot['tipo_descuento'] ?? 'ninguno' }}</td>
                        </tr>
                        <tr>
                            <td>Base del descuento:</td>
                            <td>{{ $planSnapshot['base_descuento'] ?? 'ninguna' }}</td>
                        </tr>
                        <tr>
                            <td>Descuento configurado:</td>
                            <td>{{ $descuentoConfigurado }}</td>
                        </tr>
                        <tr>
                            <td>Descuento aplicado:</td>
                            <td>${{ number_format($valorDescuento, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="section">
                <div class="section-title">{{ $seccion++ }}. DESGLOSE ECONÓMICO</div>
                <div class="section-content">
                    <table class="table">
                        @if($esSeparacion)
                        <tr>
                            <td>Fecha límite de separación:</td>
                            <td>
                                {{ $venta->fecha_limite_separacion ? Carbon::parse($venta->fecha_limite_separacion)->format('d/m/Y') : '—' }}
                            </td>
                        </tr>
                        @endif

                        <tr>
                            <td>{{ $esApartamento ? 'Valor apartamento' : 'Valor local' }}:</td>
                            <td>${{ number_format($valorBase ?: $valorTotal, 0, ',', '.') }}</td>
                        </tr>

                        <tr>
                            <td>Valor parqueadero adicional:</td>
                            <td>${{ number_format($valorParqueadero, 0, ',', '.') }}</td>
                        </tr>

                        <tr>
                            <td>Valor total sin descuento:</td>
                            <td>${{ number_format($valorTotalSinDescuento, 0, ',', '.') }}</td>
                        </tr>

                        <tr>
                            <td>Descuento aplicado:</td>
                            <td>${{ number_format($valorDescuento, 0, ',', '.') }}</td>
                        </tr>

                        <tr class="total-row">
                            <td>Valor total / valor cotizado:</td>
                            <td><strong>${{ number_format($valorTotal, 0, ',', '.') }}</strong></td>
                        </tr>

                        @if($esVenta)
                        <tr>
                            <td>Cuota de separación:</td>
                            <td>${{ number_format($valorSeparacion, 0, ',', '.') }}</td>
                        </tr>

                        <tr>
                            <td>Cuota inicial:</td>
                            <td>${{ number_format($cuotaInicial, 0, ',', '.') }}</td>
                        </tr>

                        <tr>
                            <td>Saldo cuota inicial:</td>
                            <td>${{ number_format($saldoCuotaInicial, 0, ',', '.') }}</td>
                        </tr>

                        <tr>
                            <td>Plazo cuota inicial:</td>
                            <td>{{ $venta->plazo_cuota_inicial_meses ?? '—' }} meses</td>
                        </tr>

                        <tr>
                            <td>Frecuencia pago cuota inicial:</td>
                            <td>{{ $frecuenciaTexto }}</td>
                        </tr>

                        <tr>
                            <td>No. cuotas cuota inicial:</td>
                            <td>{{ $numeroCuotasCI }}</td>
                        </tr>

                        <tr>
                            <td>Valor cuota inicial:</td>
                            <td>${{ number_format($valorCuotaCI, 0, ',', '.') }}</td>
                        </tr>

                        <tr>
                            <td>Cuotas manuales CI:</td>
                            <td>{{ $textoCuotasManualCI }}</td>
                        </tr>

                        <tr class="total-row">
                            <td>Valor restante:</td>
                            <td><strong>${{ number_format($valorRestante, 0, ',', '.') }}</strong></td>
                        </tr>
                        @else
                        <tr>
                            <td>Valor de separación:</td>
                            <td>${{ number_format($venta->valor_separacion, 0, ',', '.') }}</td>
                        </tr>
                        @endif

                        <tr>
                            <td>Forma de pago:</td>
                            <td>{{ $venta->formaPago->forma_pago ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>Fecha operación:</td>
                            <td>{{ $venta->fecha_venta ? Carbon::parse($venta->fecha_venta)->format('d/m/Y') : '—' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($esVenta && $cuotasPlan->count() > 0)
            <div class="section">
                <div class="section-title">{{ $seccion++ }}. PLAN DE PAGOS GENERADO</div>
                <div class="section-content">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Concepto</th>
                                <th>Valor cuota</th>
                                <th>Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cuotasPlan->sortBy('numero_cuota') as $cuota)
                            <tr>
                                <td>{{ $cuota->numero_cuota }}</td>
                                <td>
                                    {{ $cuota->fecha_vencimiento ? Carbon::parse($cuota->fecha_vencimiento)->format('d/m/Y') : '—' }}
                                </td>
                                <td>{{ $cuota->concepto ?? 'Cuota' }}</td>
                                <td>${{ number_format($cuota->valor_cuota ?? 0, 0, ',', '.') }}</td>
                                <td>${{ number_format($cuota->saldo ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <p class="small-text" style="margin-top: 10px;">
                        La separación puede aparecer como primera cuota informativa. El número de cuotas comerciales corresponde al plazo de cuota inicial definido en la operación.
                    </p>
                </div>
            </div>
            @endif

            @if($esEmpleado || $esAdministrativo)
            <div class="section">
                <div class="section-title">{{ $seccion++ }}. OBSERVACIONES DE LA OPERACIÓN</div>
                <div class="section-content">
                    {{ $venta->descripcion ?: 'Sin observaciones registradas.' }}
                </div>
            </div>
            @endif
        </div>

        <div class="footer">
            <p>Generado: {{ now()->format('d/m/Y H:i:s') }}</p>
            <p>Constructora A&C - Todos los derechos reservados</p>
            <p style="font-size: 10px; margin-top: 10px;">Este es un mensaje automático, por favor no responder.</p>
        </div>
    </div>
</body>

</html>