<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\HealthcheckController;
use App\Http\Controllers\Auth\EmpleadoAuthController;

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

// Route::middleware([
//     'auth:sanctum',
//     config('jetstream.auth_session'),
//     'verified',
// ])->group(function () {
//     Route::get('/dashboard', function () {
//         return Inertia::render('Dashboard');
//     })->name('dashboard');
// });

// Route::get('/health', HealthcheckController::class)
//     ->middleware(['auth', 'verified'])
//     ->name('health');

Route::get('/login', [EmpleadoAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [EmpleadoAuthController::class, 'login']);
Route::post('/logout', [EmpleadoAuthController::class, 'logout'])->name('logout');

// Ruta solo para Gerentes
Route::middleware(['auth', 'check.cargo:Gerente General'])->group(function () {
    Route::get('/gerencia', function () {
        return "Bienvenido, Gerente General!";
    })->name('gerencia');
});

// Ruta para Asesores Comerciales
Route::middleware(['auth', 'check.cargo:Asesor Comercial'])->group(function () {
    Route::get('/ventas', function () {
        return "Bienvenido, Asesor Comercial!";
    })->name('ventas');
});

// Ruta para cualquier empleado autenticado
Route::middleware(['auth'])->group(function () {
    Route::get('/perfil', function () {
        return "Tu perfil, " . Auth::user()->nombre;
    })->name('perfil');
});
