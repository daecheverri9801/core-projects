<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $empleado = $request->user()->load('cargo'); // Carga relación cargo

        return Inertia::render('Admin/Dashboard', [
            'empleado' => [
                'nombre' => $empleado->nombre, 
                'apellido' => $empleado->apellido,
                'cargo' => [
                    'nombre' => $empleado->cargo->nombre,
                ],
            ],
        ]);
    }
}