<?php

namespace App\Notifications;

use App\Models\Venta;
use App\Helpers\FormatoHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;

class VentaCreadaCliente extends Notification
{
    use Queueable;

    protected $venta;

    public function __construct(Venta $venta)
    {
        $this->venta = $venta;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $v = $this->venta;
        $proyecto = $v->proyecto;
        $cliente = $v->cliente;
        $empleado = $v->empleado;
        $esApto = !is_null($v->apartamento);
        $inm = $v->apartamento ?? $v->local;

        // Helper local para formatear dentro del blade
        $f = new FormatoHelper();

        // Determinar tipo de operación
        $tipoOperacion = $v->tipo_operacion === 'venta' ? 'VENTA' : 'SEPARACIÓN';

        // Información del inmueble
        if ($esApto) {
            $tipoInmueble = $inm->tipo_apartamento->nombre ?? $inm->tipoApartamento->nombre ?? 'Apartamento';
            $habitaciones = $inm->tipo_apartamento->cantidad_habitaciones ?? $inm->tipoApartamento->cantidad_habitaciones ?? '—';
            $banos = $inm->tipo_apartamento->cantidad_banos ?? $inm->tipoApartamento->cantidad_banos ?? '—';
            $areaConstruida = $inm->tipo_apartamento->area_construida ?? $inm->tipoApartamento->area_construida ?? '—';
            $areaPrivada = $inm->tipo_apartamento->area_privada ?? $inm->tipoApartamento->area_privada ?? '—';
            $parqueadero = FormatoHelper::parqueaderoTexto($v);
        } else {
            $tipoInmueble = 'Local Comercial';
            $habitaciones = $banos = $areaPrivada = '—';
            $areaConstruida = $inm->area_total_local ?? '—';
            $parqueadero = 'No aplica';
        }

        // Valores económicos
        $valorTotal = floatval($v->valor_total ?? 0);
        $cuotaInicial = floatval($v->cuota_inicial ?? 0);
        $valorRestante = floatval($v->valor_restante ?? max(0, $valorTotal - $cuotaInicial));
        $cuotaSep = floatval($proyecto->valor_min_separacion ?? 0);
        $saldoCuotaInicial = max(0, $cuotaInicial - $cuotaSep);

        // Construir el correo
        return (new MailMessage)
            ->subject("REPORTE DE OPERACIÓN - {$tipoOperacion} - Constructora A&C")
            ->greeting(" ")
            ->line(new HtmlString('
                <div style="background-color: #f8fafc; padding: 30px 20px; font-family: Helvetica, Arial, sans-serif;">
                    
                    <!-- Header estilo PDF -->
                    <div style="text-align: center; margin-bottom: 25px;">
                        <h1 style="color: #1e3a5f; font-size: 24px; font-weight: bold; margin: 0;">REPORTE DE OPERACIÓN</h1>
                        <p style="color: #666; font-size: 14px; margin-top: 5px;">' . strtoupper($tipoInmueble) . ' · ' . $tipoOperacion . '</p>
                        <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">
                    </div>

                    <!-- Mensaje personalizado -->
                    <div style="margin-bottom: 25px;">
                        <p style="font-size: 16px; color: #333;">Estimado(a) <strong>' . FormatoHelper::safe($cliente->nombre ?? 'cliente') . '</strong>,</p>
                        <p style="font-size: 14px; color: #555;">Le informamos que se ha registrado una operación en nuestro sistema con los siguientes detalles:</p>
                    </div>
            '))
            
            // 1. Datos del Proyecto
            ->line(new HtmlString('
                    <div style="margin-top: 20px;">
                        <h3 style="background-color: #f1f5f9; color: #1e3a5f; padding: 8px 15px; border-radius: 4px; font-size: 16px;">1. DATOS DEL PROYECTO</h3>
                        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                            <tr><td style="padding: 8px; background: #f8fafc; width: 30%; font-weight: bold;">Nombre:</td><td style="padding: 8px;">' . FormatoHelper::safe($proyecto->nombre ?? '') . '</td></tr>
                            <tr><td style="padding: 8px; background: #f8fafc; width: 30%; font-weight: bold;">Ubicación:</td><td style="padding: 8px;">' . FormatoHelper::safe(($proyecto->ubicacion->barrio ?? '—') . ' - ' . ($proyecto->ubicacion->direccion ?? '')) . '</td></tr>
                            <tr><td style="padding: 8px; background: #f8fafc; width: 30%; font-weight: bold;">Zonas sociales:</td><td style="padding: 8px;">' . FormatoHelper::getZonas($proyecto) . '</td></tr>
                        </table>
                    </div>
            '))
            
            // 2. Datos del Cliente
            ->line(new HtmlString('
                    <div>
                        <h3 style="background-color: #f1f5f9; color: #1e3a5f; padding: 8px 15px; border-radius: 4px; font-size: 16px;">2. DATOS DEL CLIENTE</h3>
                        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                            <tr><td style="padding: 8px; background: #f8fafc; width: 30%; font-weight: bold;">Nombre:</td><td style="padding: 8px;">' . FormatoHelper::safe($cliente->nombre ?? '') . '</td></tr>
                            <tr><td style="padding: 8px; background: #f8fafc; width: 30%; font-weight: bold;">Documento:</td><td style="padding: 8px;">' . FormatoHelper::safe($v->documento_cliente ?? '') . '</td></tr>
                            <tr><td style="padding: 8px; background: #f8fafc; width: 30%; font-weight: bold;">Teléfono:</td><td style="padding: 8px;">' . FormatoHelper::safe($cliente->telefono ?? '') . '</td></tr>
                            <tr><td style="padding: 8px; background: #f8fafc; width: 30%; font-weight: bold;">Correo:</td><td style="padding: 8px;">' . FormatoHelper::safe($cliente->correo ?? '') . '</td></tr>
                            <tr><td style="padding: 8px; background: #f8fafc; width: 30%; font-weight: bold;">Dirección:</td><td style="padding: 8px;">' . FormatoHelper::safe($cliente->direccion ?? '') . '</td></tr>
                        </table>
                    </div>
            '))
            
            // 3. Datos del Asesor
            ->line(new HtmlString('
                    <div>
                        <h3 style="background-color: #f1f5f9; color: #1e3a5f; padding: 8px 15px; border-radius: 4px; font-size: 16px;">3. DATOS DEL ASESOR</h3>
                        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                            <tr><td style="padding: 8px; background: #f8fafc; width: 30%; font-weight: bold;">Nombre:</td><td style="padding: 8px;">' . FormatoHelper::safe(($empleado->nombre ?? '') . ' ' . ($empleado->apellido ?? '')) . '</td></tr>
                            <tr><td style="padding: 8px; background: #f8fafc; width: 30%; font-weight: bold;">Correo:</td><td style="padding: 8px;">' . FormatoHelper::safe($empleado->email ?? '') . '</td></tr>
                            <tr><td style="padding: 8px; background: #f8fafc; width: 30%; font-weight: bold;">Teléfono:</td><td style="padding: 8px;">' . FormatoHelper::safe($empleado->telefono ?? '') . '</td></tr>
                        </table>
                    </div>
            '))
            
            // 4. Información del Inmueble
            ->line(new HtmlString('
                    <div>
                        <h3 style="background-color: #f1f5f9; color: #1e3a5f; padding: 8px 15px; border-radius: 4px; font-size: 16px;">4. INFORMACIÓN DEL INMUEBLE</h3>
                        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                            <tr><td style="padding: 8px; background: #f8fafc; width: 30%; font-weight: bold;">Tipo:</td><td style="padding: 8px;">' . $tipoInmueble . '</td></tr>
                            <tr><td style="padding: 8px; background: #f8fafc; width: 30%; font-weight: bold;">Número:</td><td style="padding: 8px;">' . FormatoHelper::safe($inm->numero ?? '') . '</td></tr>
                            <tr><td style="padding: 8px; background: #f8fafc; width: 30%; font-weight: bold;">Torre:</td><td style="padding: 8px;">' . FormatoHelper::safe($inm->torre->nombre_torre ?? '') . '</td></tr>
                            <tr><td style="padding: 8px; background: #f8fafc; width: 30%; font-weight: bold;">Piso:</td><td style="padding: 8px;">' . FormatoHelper::safe($inm->piso_torre->nivel ?? '') . '</td></tr>
            '))
            
            ->line(new HtmlString($esApto ? '
                            <tr><td style="padding: 8px; background: #f8fafc; width: 30%; font-weight: bold;">Alcobas:</td><td style="padding: 8px;">' . $habitaciones . '</td></tr>
                            <tr><td style="padding: 8px; background: #f8fafc; width: 30%; font-weight: bold;">Baños:</td><td style="padding: 8px;">' . $banos . '</td></tr>
                            <tr><td style="padding: 8px; background: #f8fafc; width: 30%; font-weight: bold;">Área construida:</td><td style="padding: 8px;">' . $areaConstruida . ' m²</td></tr>
                            <tr><td style="padding: 8px; background: #f8fafc; width: 30%; font-weight: bold;">Área privada:</td><td style="padding: 8px;">' . $areaPrivada . ' m²</td></tr>
                            <tr><td style="padding: 8px; background: #f8fafc; width: 30%; font-weight: bold;">Parqueadero:</td><td style="padding: 8px;">' . $parqueadero . '</td></tr>
                            <tr><td style="padding: 8px; background: #f8fafc; width: 30%; font-weight: bold;">Parqueadero adicional:</td><td style="padding: 8px;">' . ($v->parqueadero ? 'Sí' : 'No') . '</td></tr>
            ' : '
                            <tr><td style="padding: 8px; background: #f8fafc; width: 30%; font-weight: bold;">Área total:</td><td style="padding: 8px;">' . $areaConstruida . ' m²</td></tr>
            ') . '</table></div>')
            
            // 5. Desglose Económico
            ->line(new HtmlString('
                    <div>
                        <h3 style="background-color: #f1f5f9; color: #1e3a5f; padding: 8px 15px; border-radius: 4px; font-size: 16px;">5. DESGLOSE ECONÓMICO</h3>
                        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                            <tr><td style="padding: 8px; background: #f8fafc; width: 50%; font-weight: bold;">Fecha Límite de Separación:</td><td style="padding: 8px;">' . ($v->tipo_operacion === 'separacion' ? FormatoHelper::date($v->fecha_limite_separacion) : 'No Aplica') . '</td></tr>
                            <tr><td style="padding: 8px; background: #f8fafc; width: 50%; font-weight: bold;">Valor apartamento:</td><td style="padding: 8px;">' . FormatoHelper::money($v->valor_base ?? $v->valor_total) . '</td></tr>
                            <tr><td style="padding: 8px; background: #f8fafc; width: 50%; font-weight: bold;">Valor parqueadero:</td><td style="padding: 8px;">' . FormatoHelper::money($v->parqueadero->precio ?? 0) . '</td></tr>
                            <tr><td style="padding: 8px; background: #f8fafc; width: 50%; font-weight: bold; border-top: 2px solid #ddd;">Valor total:</td><td style="padding: 8px; border-top: 2px solid #ddd;"><strong>' . FormatoHelper::money($valorTotal) . '</strong></td></tr>
            '))
            
            ->line(new HtmlString(($v->tipo_operacion === 'venta' ? '
                            <tr><td style="padding: 8px; background: #f8fafc; width: 50%; font-weight: bold;">Cuota inicial:</td><td style="padding: 8px;">' . FormatoHelper::money($cuotaInicial) . '</td></tr>
                            <tr><td style="padding: 8px; background: #f8fafc; width: 50%; font-weight: bold;">Plazo cuota inicial (meses):</td><td style="padding: 8px;">' . FormatoHelper::safe($v->plazo_cuota_inicial_meses) . '</td></tr>
                            <tr><td style="padding: 8px; background: #f8fafc; width: 50%; font-weight: bold;">Frecuencia pago:</td><td style="padding: 8px;">Cada ' . FormatoHelper::safe($v->frecuencia_cuota_inicial_meses) . ' meses</td></tr>
                            <tr><td style="padding: 8px; background: #f8fafc; width: 50%; font-weight: bold;">Saldo restante:</td><td style="padding: 8px;">' . FormatoHelper::money($valorRestante) . '</td></tr>
            ' : '
                            <tr><td style="padding: 8px; background: #f8fafc; width: 50%; font-weight: bold;">Valor de separación:</td><td style="padding: 8px;">' . FormatoHelper::money($v->valor_separacion ?? 0) . '</td></tr>
            ') . '
                            <tr><td style="padding: 8px; background: #f8fafc; width: 50%; font-weight: bold;">Forma de pago:</td><td style="padding: 8px;">' . FormatoHelper::safe($v->forma_pago->forma_pago ?? $v->formaPago->forma_pago ?? '') . '</td></tr>
                            <tr><td style="padding: 8px; background: #f8fafc; width: 50%; font-weight: bold;">Fecha operación:</td><td style="padding: 8px;">' . FormatoHelper::date($v->fecha_venta) . '</td></tr>
                        </table>
                    </div>
            '))
            
            // Footer
            ->line(new HtmlString('
                    <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0 20px;">
                    <div style="text-align: center; color: #666; font-size: 12px;">
                        <p>Generado: ' . now()->format('d/m/Y H:i:s') . '</p>
                        <p style="margin-top: 5px;">Constructora A&C - Todos los derechos reservados</p>
                        <p style="margin-top: 5px; font-size: 11px;">Este es un mensaje automático, por favor no responder.</p>
                    </div>
                </div>
            '));
    }
}