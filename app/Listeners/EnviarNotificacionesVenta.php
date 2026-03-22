<?php

namespace App\Listeners;

use App\Events\VentaCreada;
use App\Notifications\VentaCreadaCliente;
use App\Notifications\VentaCreadaEmpleado;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\AnonymousNotifiable;

class EnviarNotificacionesVenta implements ShouldQueue
{
    use InteractsWithQueue;

    // CORREOS FIJOS (fallback / siempre enviar)
    protected $emailsGerentes = [
        'gerencia@constructora-ayc.com',
    ];

    protected $emailsContabilidad = [
        'contabilidad@constructora-ayc.com',
    ];

    // También puedes tener un array de copia oculta (BCC) general
    protected $emailsSiempre = [
        'daecheverri98@gmail.com',
    ];

    public function handle(VentaCreada $event)
    {
        $venta = $event->venta;

        Log::info('📧 Procesando notificaciones para venta #' . $venta->id_venta);

        // 1. Cliente
        $this->enviarAlCliente($venta);

        // 2. Empleado asesor (si existe en BD)
        $this->enviarAlEmpleado($venta);

        // 3. Gerentes (BD + correos fijos)
        $this->enviarAGerentes($venta);

        // 4. Contabilidad (BD + correos fijos)
        $this->enviarAContabilidad($venta);

        // 5. Siempre enviar a correos adicionales
        $this->enviarACorreosSiempre($venta);
    }

    protected function enviarAlCliente($venta)
    {
        if ($venta->cliente && !empty($venta->cliente->correo)) {
            try {
                $venta->cliente->notify(new VentaCreadaCliente($venta));
                Log::info('✅ Cliente: ' . $venta->cliente->correo);
            } catch (\Exception $e) {
                Log::error('❌ Error cliente: ' . $e->getMessage());
            }
        } else {
            Log::warning('⚠️ Cliente sin correo registrado');
        }
    }

    protected function enviarAlEmpleado($venta)
    {
        if ($venta->empleado && !empty($venta->empleado->email)) {
            try {
                $venta->empleado->notify(new VentaCreadaEmpleado($venta));
                Log::info('✅ Empleado: ' . $venta->empleado->email);
            } catch (\Exception $e) {
                Log::error('❌ Error empleado: ' . $e->getMessage());
            }
        } else {
            Log::warning('⚠️ Empleado sin email registrado');
        }
    }

    protected function enviarAGerentes($venta)
    {
        foreach ($this->emailsGerentes as $email) {
            try {
                (new AnonymousNotifiable)
                    ->route('mail', $email)
                    ->notify(new VentaCreadaEmpleado($venta));

                Log::info('✅ Notificación enviada a gerente: ' . $email);
            } catch (\Exception $e) {
                Log::error('❌ Error enviando a gerente ' . $email . ': ' . $e->getMessage());
            }
        }
    }

    protected function enviarAContabilidad($venta)
    {
        foreach ($this->emailsContabilidad as $email) {
            try {
                (new AnonymousNotifiable)
                    ->route('mail', $email)
                    ->notify(new VentaCreadaEmpleado($venta));

                Log::info('✅ Notificación enviada a contabilidad: ' . $email);
            } catch (\Exception $e) {
                Log::error('❌ Error enviando a contabilidad ' . $email . ': ' . $e->getMessage());
            }
        }
    }

    protected function enviarACorreosSiempre($venta)
    {
        foreach ($this->emailsSiempre as $email) {
            try {
                (new AnonymousNotifiable)
                    ->route('mail', $email)
                    ->notify(new VentaCreadaEmpleado($venta));

                Log::info('✅ Notificación enviada a contabilidad: ' . $email);
            } catch (\Exception $e) {
                Log::error('❌ Error enviando a contabilidad ' . $email . ': ' . $e->getMessage());
            }
        }
    }

    // // 1. Enviar correo al CLIENTE (si tiene email)
    // if ($venta->cliente && $venta->cliente->correo) {
    //     $venta->cliente->notify(new VentaCreadaCliente($venta));
    // }

    // // 2. Enviar correo al EMPLEADO (asesor)
    // if ($venta->empleado && $venta->empleado->email) {
    //     $venta->empleado->notify(new VentaCreadaEmpleado($venta));
    // }

    // 3. (Opcional) Enviar correo a administradores
    // $admins = User::where('rol', 'admin')->get();
    // foreach ($admins as $admin) {
    //     $admin->notify(new VentaCreadaEmpleado($venta));
    // }
    // }
}
