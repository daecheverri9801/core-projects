<?php

namespace App\Http\Controllers\Gerencia;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LoginLogController extends Controller
{
    public function index(Request $request)
    {
        $empleado = $request->user()->load('cargo');

        $q = trim((string) $request->query('q', ''));
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        $logs = LoginLog::query()
            ->with(['empleado:id_empleado,nombre,apellido,email'])
            ->when($q, function ($query) use ($q) {
                $query->whereHas('empleado', function ($sq) use ($q) {
                    $sq->where('nombre', 'ILIKE', "%{$q}%")
                        ->orWhere('apellido', 'ILIKE', "%{$q}%")
                        ->orWhere('email', 'ILIKE', "%{$q}%");
                })
                    ->orWhere('ip', 'ILIKE', "%{$q}%");
            })
            ->when($desde, fn($query) => $query->whereDate('logged_in_at', '>=', $desde))
            ->when($hasta, fn($query) => $query->whereDate('logged_in_at', '<=', $hasta))
            ->orderByDesc('logged_in_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Gerencia/LoginLogs/Index', [
            'empleado' => $empleado,
            'logs' => $logs,
            'filters' => [
                'q' => $q,
                'desde' => $desde,
                'hasta' => $hasta,
            ],
        ]);
    }
}
