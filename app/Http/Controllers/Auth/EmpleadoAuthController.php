<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmpleadoAuthController extends Controller
{
    /**
     * Mostrar formulario de login para empleados
     */
    public function showLoginForm()
    {
        return view('auth.empleado-login'); // Crear esta vista
    }

    /**
     * Procesar login de empleado
     */
    public function login(Request $request)
    {
        // Validar datos
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Intentar login con guard 'web' (configurado para empleados)
        if (Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Redirigir a la ruta deseada después del login
            return redirect()->intended('/dashboard');
        }

        // Si falla, regresar con error
        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    /**
     * Cerrar sesión del empleado
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}