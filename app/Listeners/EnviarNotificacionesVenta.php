<?php

namespace App\Listeners;

use App\Events\VentaCreada;
use App\Notifications\VentaCreadaCliente;
use App\Notifications\VentaCreadaEmpleado;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class EnviarNotificacionesVenta implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(VentaCreada $event)
    {
        $venta = $event->venta;
        
        // 1. Enviar correo al CLIENTE (si tiene email)
        if ($venta->cliente && $venta->cliente->correo) {
            $venta->cliente->notify(new VentaCreadaCliente($venta));
        }
        
        // 2. Enviar correo al EMPLEADO (asesor)
        if ($venta->empleado && $venta->empleado->correo) {
            $venta->empleado->notify(new VentaCreadaEmpleado($venta));
        }
        
        // 3. (Opcional) Enviar correo a administradores
        // $admins = User::where('rol', 'admin')->get();
        // foreach ($admins as $admin) {
        //     $admin->notify(new VentaCreadaEmpleado($venta));
        // }
    }
}