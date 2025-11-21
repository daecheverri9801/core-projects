<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Amortización</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #999; padding: 6px; text-align: center; }
        th { background: #eee; }
    </style>
</head>
<body>

<h2>Plan de Amortización - Venta #{{ $venta->id_venta }}</h2>

<p><b>Apartamento:</b> {{ $venta->apartamento->numero }}</p>
<p><b>Fecha Venta:</b> {{ $venta->fecha_venta }}</p>
<p><b>Plazo:</b> {{ $venta->plazo_cuota_inicial_meses }} meses</p>
<p><b>Valor Total:</b> ${{ number_format($venta->valor_total,0,',','.') }}</p>
<p><b>Cuota Inicial:</b> ${{ number_format($venta->cuota_inicial,0,',','.') }}</p>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Fecha Cuota</th>
            <th>Valor Cuota</th>
            <th>Saldo</th>
        </tr>
    </thead>
    <tbody>
        @foreach($venta->planAmortizacion->cuotas as $c)
        <tr>
            <td>{{ $c->numero_cuota }}</td>
            <td>{{ $c->fecha_cuota }}</td>
            <td>${{ number_format($c->valor_cuota,0,',','.') }}</td>
            <td>${{ number_format($c->saldo,0,',','.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
