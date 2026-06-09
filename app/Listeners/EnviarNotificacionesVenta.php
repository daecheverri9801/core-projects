<?php

namespace App\Listeners;

use App\Events\VentaCreada;
use App\Mail\VentaNotificacionMailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnviarNotificacionesVenta implements ShouldQueue
{
    use InteractsWithQueue;

    protected $emailsGerentes = ['gerencia@constructora-ayc.com'];
    protected $emailsContabilidad = ['contabilidad@constructora-ayc.com'];
    protected $emailsSiempre = ['daecheverri98@gmail.com'];

    public function handle(VentaCreada $event)
    {
        $venta = $event->venta;

        Log::info('📧 Procesando notificaciones para venta #' . $venta->id_venta);

        // Cliente
        if ($venta->cliente && !empty($venta->cliente->correo)) {
            try {
                Mail::to($venta->cliente->correo)->send(new VentaNotificacionMailable($venta, 'cliente'));
                Log::info('✅ Cliente: ' . $venta->cliente->correo);
            } catch (\Exception $e) {
                Log::error('❌ Error cliente: ' . $e->getMessage());
            }
        } else {
            Log::warning('⚠️ Cliente sin correo registrado');
        }

        // Empleado
        if ($venta->empleado && !empty($venta->empleado->email)) {
            try {
                Mail::to($venta->empleado->email)->send(new VentaNotificacionMailable($venta, 'empleado'));
                Log::info('✅ Empleado: ' . $venta->empleado->email);
            } catch (\Exception $e) {
                Log::error('❌ Error empleado: ' . $e->getMessage());
            }
        } else {
            Log::warning('⚠️ Empleado sin email registrado');
        }

        // Gerentes
        foreach ($this->emailsGerentes as $email) {
            try {
                Mail::to($email)->send(new VentaNotificacionMailable($venta, 'admin'));
                Log::info('✅ Gerente: ' . $email);
            } catch (\Exception $e) {
                Log::error('❌ Error gerente: ' . $e->getMessage());
            }
        }

        // Contabilidad
        foreach ($this->emailsContabilidad as $email) {
            try {
                Mail::to($email)->send(new VentaNotificacionMailable($venta, 'admin'));
                Log::info('✅ Contabilidad: ' . $email);
            } catch (\Exception $e) {
                Log::error('❌ Error contabilidad: ' . $e->getMessage());
            }
        }

        // Siempre
        foreach ($this->emailsSiempre as $email) {
            try {
                Mail::to($email)->send(new VentaNotificacionMailable($venta, 'admin'));
                Log::info('✅ Siempre: ' . $email);
            } catch (\Exception $e) {
                Log::error('❌ Error siempre: ' . $e->getMessage());
            }
        }
    }
}
