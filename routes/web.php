<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\EmpleadoAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProyectoController;
use App\Http\Controllers\Admin\EmpleadoController;
use App\Http\Controllers\Admin\EstadosController;
use App\Http\Controllers\Admin\DependenciasCargosController;
use App\Http\Controllers\Auth\EmpleadoPasswordResetController;
use Illuminate\Support\Facades\Mail;

Route::get('/', [EmpleadoAuthController::class, 'showLoginForm'])->name('home');
Route::post('/login', [EmpleadoAuthController::class, 'login'])->name('login');
Route::post('/logout', [EmpleadoAuthController::class, 'logout'])->name('logout');

Route::middleware('guest')->group(function () {
    Route::get('/empleado/forgot-password', [EmpleadoPasswordResetController::class, 'showLinkRequestForm'])->name('empleado.password.request');
    Route::post('/empleado/forgot-password', [EmpleadoPasswordResetController::class, 'sendResetLinkEmail'])->name('empleado.password.email');
    Route::get('/empleado/reset-password/{token}', [EmpleadoPasswordResetController::class, 'showResetForm'])->name('empleado.password.reset');
    Route::post('/empleado/reset-password', [EmpleadoPasswordResetController::class, 'reset'])->name('empleado.password.update');
});

// Ruta solo para Administradores
Route::middleware(['auth', 'check.cargo:Administrador'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::prefix('proyectos')->group(function () {
        Route::get('/', [ProyectoController::class, 'index'])->name('proyectos.index');
        Route::get('/create', [ProyectoController::class, 'create'])->name('proyectos.create');
        Route::post('/', [ProyectoController::class, 'store'])->name('proyectos.store');
        Route::get('/{proyecto}', [ProyectoController::class, 'show'])->name('proyectos.show');
        Route::get('/{proyecto}/edit', [ProyectoController::class, 'edit'])->name('proyectos.edit');
        Route::put('/{proyecto}', [ProyectoController::class, 'update'])->name('proyectos.update');
        Route::delete('/{proyecto}', [ProyectoController::class, 'destroy'])->name('proyectos.destroy');
    });
    Route::resource('empleados', EmpleadoController::class);
    Route::get('/estados', [EstadosController::class, 'index'])->name('estados.index');
    // Estados
    Route::post('/estados', [EstadosController::class, 'storeEstado'])->name('estados.store');
    Route::put('/estados/{id}', [EstadosController::class, 'updateEstado'])->name('estados.update');
    Route::delete('/estados/{id}', [EstadosController::class, 'destroyEstado'])->name('estados.destroy');
    // Estados Inmueble
    Route::post('/estados-inmueble', [EstadosController::class, 'storeEstadoInmueble'])->name('estados-inmueble.store');
    Route::put('/estados-inmueble/{id}', [EstadosController::class, 'updateEstadoInmueble'])->name('estados-inmueble.update');
    Route::delete('/estados-inmueble/{id}', [EstadosController::class, 'destroyEstadoInmueble'])->name('estados-inmueble.destroy');
    Route::get('/dependencias-cargos', [DependenciasCargosController::class, 'index'])->name('dependencias-cargos.index');
    // Dependencias
    Route::post('/dependencias', [DependenciasCargosController::class, 'storeDependencia'])->name('dependencias.store');
    Route::put('/dependencias/{id}', [DependenciasCargosController::class, 'updateDependencia'])->name('dependencias.update');
    Route::delete('/dependencias/{id}', [DependenciasCargosController::class, 'destroyDependencia'])->name('dependencias.destroy');
    // Cargos
    Route::post('/cargos', [DependenciasCargosController::class, 'storeCargo'])->name('cargos.store');
    Route::put('/cargos/{id}', [DependenciasCargosController::class, 'updateCargo'])->name('cargos.update');
    Route::delete('/cargos/{id}', [DependenciasCargosController::class, 'destroyCargo'])->name('cargos.destroy');
});

// Ruta para cualquier empleado autenticado
Route::middleware(['auth'])->group(function () {
    Route::get('/perfil', function () {
        return "Tu perfil, " . Auth::user()->nombre;
    })->name('perfil');
});

Route::get('/test-email', function () {
    Mail::raw('Este es un correo de prueba', function ($message) {
        $message->to('daniel.arango20125@ucaldas.edu.co')
                ->subject('Correo de prueba');
    });

    return 'Correo enviado';
});
