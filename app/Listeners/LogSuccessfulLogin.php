<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\LoginLog;

class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        $request = request();

        $user = $event->user; // Empleado (tu Authenticatable)
        $idEmpleado = $user?->id_empleado ?? null;

        LoginLog::create([
            'id_empleado'   => $idEmpleado,
            'guard_name'    => $event->guard,
            'ip'            => $request?->ip(),
            'user_agent'    => substr((string) $request?->userAgent(), 0, 2000),
            'logged_in_at'  => now(),
        ]);
    }
}
