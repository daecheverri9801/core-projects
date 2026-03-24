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
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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

        .section {
            margin-bottom: 25px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            overflow: hidden;
        }

        .section-title {
            background-color: #1e3a5f;
            color: white;
            padding: 10px 15px;
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }

        .section-content {
            padding: 15px;
            background: #fafafa;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table td {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
        }

        .table td:first-child {
            font-weight: bold;
            width: 40%;
            background-color: #f5f5f5;
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        .total-row td {
            border-top: 2px solid #1e3a5f;
            font-weight: bold;
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
    $esEmpleado = $tipo === 'empleado';
    $esCliente = $tipo === 'cliente';
    $esAdministrativo = $tipo === 'administrativo';
    $esApartamento = !is_null($venta->apartamento);
    $tituloOperacion = $venta->tipo_operacion == 'venta' ? 'VENTA' : 'SEPARACIÓN';
    $tipoInmueble = $esApartamento ? 'Apartamento' : 'Local';
    @endphp

    <div class="container">
        <div class="header">
            <img src="{{ asset('images/logo-ayc.png') }}" alt="Constructora A&C" class="header-logo">
            <h1>REPORTE DE OPERACIÓN</h1>
            <p>{{ $tituloOperacion }} · {{ $tipoInmueble }}</p>
        </div>

        <div class="content">
            @if($esEmpleado)
            <p style="margin-bottom: 20px;">
                Estimado(a) <strong>{{ trim(($venta->empleado->nombre ?? '') . ' ' . ($venta->empleado->apellido ?? '')) ?: 'Empleado' }}</strong>,
            </p>
            <p style="margin-bottom: 25px;">
                Se ha registrado una nueva operación en el sistema con los siguientes detalles:
            </p>
            @elseif($esCliente)
            <p style="margin-bottom: 20px;">
                Estimado(a) <strong>{{ $venta->cliente->nombre ?? 'Cliente' }}</strong>,
            </p>
            <p style="margin-bottom: 25px;">
                Le informamos que se ha registrado una operación en nuestro sistema con los siguientes detalles:
            </p>
            @else
            <p style="margin-bottom: 20px;">
                Estimado(a),
            </p>
            <p style="margin-bottom: 25px;">
                Se ha registrado una nueva operación en el sistema. A continuación se comparten los detalles para control administrativo:
            </p>
            @endif

            <div class="section">
                <div class="section-title">1. DATOS DEL PROYECTO</div>
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
                            <td>{{ optional($venta->proyecto->zonasSociales)->pluck('nombre')->filter()->join(', ') ?: '—' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($esEmpleado || $esAdministrativo)
            <div class="section">
                <div class="section-title">2. DATOS DEL CLIENTE</div>
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
                <div class="section-title">{{ $esCliente ? '2' : '3' }}. DATOS DEL ASESOR</div>
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
                <div class="section-title">{{ $esCliente ? '3' : '4' }}. INFORMACIÓN DEL INMUEBLE</div>
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
                            <td>Alcobas:</td>
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
                            <td>{{ $venta->apartamento && $venta->apartamento->parqueaderos && $venta->apartamento->parqueaderos->count() > 0 ? 'Sí' : 'No' }}</td>
                        </tr>
                        <tr>
                            <td>Parqueadero adicional:</td>
                            <td>{{ $venta->parqueadero ? 'Sí' : 'No' }}</td>
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
                <div class="section-title">{{ $esCliente ? '4' : '5' }}. DESGLOSE ECONÓMICO</div>
                <div class="section-content">
                    <table class="table">
                        @if($venta->tipo_operacion == 'separacion')
                        <tr>
                            <td>Fecha Límite de Separación:</td>
                            <td>{{ \Carbon\Carbon::parse($venta->fecha_limite_separacion)->format('d/m/Y') }}</td>
                        </tr>
                        @endif

                        <tr>
                            <td>{{ $esApartamento ? 'Valor apartamento' : 'Valor local' }}:</td>
                            <td>${{ number_format($venta->valor_base ?? $venta->valor_total, 0, ',', '.') }}</td>
                        </tr>

                        @if($venta->parqueadero)
                        <tr>
                            <td>Valor parqueadero:</td>
                            <td>${{ number_format($venta->parqueadero->precio ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @endif

                        <tr class="total-row">
                            <td>Valor total:</td>
                            <td><strong>${{ number_format($venta->valor_total, 0, ',', '.') }}</strong></td>
                        </tr>

                        @if($venta->tipo_operacion == 'venta')
                        <tr>
                            <td>Cuota inicial:</td>
                            <td>${{ number_format($venta->cuota_inicial, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>Plazo cuota inicial:</td>
                            <td>{{ $venta->plazo_cuota_inicial_meses }} meses</td>
                        </tr>
                        <tr>
                            <td>Frecuencia de pago:</td>
                            <td>Cada {{ $venta->frecuencia_cuota_inicial_meses }} meses</td>
                        </tr>
                        <tr>
                            <td>Saldo restante:</td>
                            <td>${{ number_format($venta->valor_restante, 0, ',', '.') }}</td>
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
                            <td>{{ \Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($esEmpleado || $esAdministrativo)
            <div class="section">
                <div class="section-title">{{ $esCliente ? '5' : '6' }}. OBSERVACIONES DE LA OPERACIÓN</div>
                <div class="section-content">
                    <div style="
                width: 100%;
                min-height: 100px;
                background-color: #ffffff;
                border: 1px solid #ddd;
                border-radius: 6px;
                padding: 12px;
                font-size: 14px;
                color: #333;
                white-space: pre-wrap;
                word-break: break-word;
                box-sizing: border-box;
            ">
                        {{ $venta->descripcion ?: 'Sin observaciones registradas.' }}
                    </div>
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