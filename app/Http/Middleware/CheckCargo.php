<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckCargo
{
    public function handle(Request $request, Closure $next, ...$cargosPermitidos)
    {
        $empleado = Auth::guard('web')->user();

        if (!$empleado || !in_array($empleado->cargo->nombre, $cargosPermitidos)) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}
