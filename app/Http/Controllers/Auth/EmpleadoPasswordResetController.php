<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use App\Models\Empleado;

class EmpleadoPasswordResetController extends Controller
{
    // Mostrar formulario para solicitar link de recuperación
    public function showLinkRequestForm()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    // Enviar email con link de recuperación
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:empleados,email']);

        $status = Password::broker('empleados')->sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }

    // Mostrar formulario para restablecer contraseña
    public function showResetForm(Request $request, $token = null)
    {
        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    // Procesar restablecimiento de contraseña
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:empleados,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::broker('empleados')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($empleado, $password) {
                $empleado->password = Hash::make($password);
                $empleado->save();
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return redirect()->route('home')->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
}