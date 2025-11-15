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
use App\Http\Controllers\Admin\UbicacionController;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Admin\AdminTorreController;
use App\Http\Controllers\Admin\PisoTorreWebController;
use App\Http\Controllers\Admin\ApartamentoWebController;
use App\Http\Controllers\Admin\TipoApartamentoWebController;
use App\Http\Controllers\Admin\LocalWebController;
use App\Http\Controllers\Admin\ZonaSocialWebController;
use App\Http\Controllers\Admin\ParqueaderoWebController;
use App\Http\Controllers\Admin\PoliticaPrecioProyectoWebController;

use App\Http\Controllers\Ventas\TipoClienteWebController;
use App\Http\Controllers\Ventas\TipoDocumentoWebController;
use App\Http\Controllers\Ventas\ClienteWebController;
use App\Http\Controllers\Ventas\FormaPagoWebController;
use App\Http\Controllers\Ventas\EstadoVentaWebController;
use App\Http\Controllers\Ventas\ConceptoPagoWebController;
use App\Http\Controllers\Ventas\MedioPagoWebController;
use App\Http\Controllers\Ventas\VentaWebController;
use App\Http\Controllers\Ventas\PlanAmortizacionVentaWebController;
use App\Http\Controllers\Ventas\PagoWebController;
use App\Http\Controllers\Ventas\PlanAmortizacionCuotaWebController;
use App\Http\Controllers\Ventas\CatalogoWebController;


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
    Route::get('/ubicacion/jerarquia', [UbicacionController::class, 'getJerarquia'])->name('ubicacion.jerarquia');
    Route::post('/ubicacion', [UbicacionController::class, 'store'])->name('ubicacion.store');

    Route::prefix('admin/torres')->name('admin.torres.')->group(function () {
        Route::get('/', [AdminTorreController::class, 'index'])->name('index');
        Route::get('/create', [AdminTorreController::class, 'create'])->name('create');
        Route::post('/', [AdminTorreController::class, 'store'])->name('store');
        Route::get('/{id_torre}', [AdminTorreController::class, 'show'])->name('show');
        Route::get('/{id_torre}/edit', [AdminTorreController::class, 'edit'])->name('edit');
        Route::put('/{id_torre}', [AdminTorreController::class, 'update'])->name('update');
        Route::delete('/{id_torre}', [AdminTorreController::class, 'destroy'])->name('destroy');
    });

    Route::get('/pisos-torre', [PisoTorreWebController::class, 'index'])->name('pisostorre.index');
    Route::get('/pisos-torre/create', [PisoTorreWebController::class, 'create'])->name('pisostorre.create');
    Route::post('/pisos-torre', [PisoTorreWebController::class, 'store'])->name('pisostorre.store');
    Route::get('/pisos-torre/{id}', [PisoTorreWebController::class, 'show'])->name('pisostorre.show');
    Route::get('/pisos-torre/{id}/edit', [PisoTorreWebController::class, 'edit'])->name('pisostorre.edit');
    Route::put('/pisos-torre/{id}', [PisoTorreWebController::class, 'update'])->name('pisostorre.update');
    Route::delete('/pisos-torre/{id}', [PisoTorreWebController::class, 'destroy'])->name('pisostorre.destroy');

    // Auxiliar para selects dependientes
    Route::get('/api/torres-por-proyecto/{id_proyecto}', [PisoTorreWebController::class, 'torresPorProyecto'])->name('pisostorre.torresPorProyecto');

    Route::get('/apartamentos', [ApartamentoWebController::class, 'index'])->name('apartamentos.index');
    Route::get('/apartamentos/create', [ApartamentoWebController::class, 'create'])->name('apartamentos.create');
    Route::post('/apartamentos', [ApartamentoWebController::class, 'store'])->name('apartamentos.store');
    Route::get('/apartamentos/{id}', [ApartamentoWebController::class, 'show'])->name('apartamentos.show');
    Route::get('/apartamentos/{id}/edit', [ApartamentoWebController::class, 'edit'])->name('apartamentos.edit');
    Route::put('/apartamentos/{id}', [ApartamentoWebController::class, 'update'])->name('apartamentos.update');
    Route::delete('/apartamentos/{id}', [ApartamentoWebController::class, 'destroy'])->name('apartamentos.destroy');

    // Auxiliares para selects encadenados
    Route::get('/api/torres-por-proyecto/{id_proyecto}', [ApartamentoWebController::class, 'torresPorProyecto'])->name('apartamentos.torresPorProyecto');
    Route::get('/api/pisos-por-torre/{id_torre}', [ApartamentoWebController::class, 'pisosPorTorre'])->name('apartamentos.pisosPorTorre');

    Route::get('/tipos-apartamento', [TipoApartamentoWebController::class, 'index'])->name('tipos-apartamento.index');
    Route::get('/tipos-apartamento/create', [TipoApartamentoWebController::class, 'create'])->name('tipos-apartamento.create');
    Route::post('/tipos-apartamento', [TipoApartamentoWebController::class, 'store'])->name('tipos-apartamento.store');
    Route::get('/tipos-apartamento/{id}', [TipoApartamentoWebController::class, 'show'])->name('tipos-apartamento.show');
    Route::get('/tipos-apartamento/{id}/edit', [TipoApartamentoWebController::class, 'edit'])->name('tipos-apartamento.edit');
    Route::put('/tipos-apartamento/{id}', [TipoApartamentoWebController::class, 'update'])->name('tipos-apartamento.update');
    Route::delete('/tipos-apartamento/{id}', [TipoApartamentoWebController::class, 'destroy'])->name('tipos-apartamento.destroy');

    Route::get('/locales', [LocalWebController::class, 'index'])->name('locales.index');
    Route::get('/locales/create', [LocalWebController::class, 'create'])->name('locales.create');
    Route::post('/locales', [LocalWebController::class, 'store'])->name('locales.store');
    Route::get('/locales/{id}', [LocalWebController::class, 'show'])->name('locales.show');
    Route::get('/locales/{id}/edit', [LocalWebController::class, 'edit'])->name('locales.edit');
    Route::put('/locales/{id}', [LocalWebController::class, 'update'])->name('locales.update');
    Route::delete('/locales/{id}', [LocalWebController::class, 'destroy'])->name('locales.destroy');

    // Auxiliares para selects encadenados
    Route::get('/api/torres-por-proyecto/{id_proyecto}', [LocalWebController::class, 'torresPorProyecto'])->name('locales.torresPorProyecto');
    Route::get('/api/pisos-por-torre/{id_torre}', [LocalWebController::class, 'pisosPorTorre'])->name('locales.pisosPorTorre');

    Route::get('/zonas-sociales', [ZonaSocialWebController::class, 'index'])->name('zonas-sociales.index');
    Route::get('/zonas-sociales/create', [ZonaSocialWebController::class, 'create'])->name('zonas-sociales.create');
    Route::post('/zonas-sociales', [ZonaSocialWebController::class, 'store'])->name('zonas-sociales.store');
    Route::get('/zonas-sociales/{id}', [ZonaSocialWebController::class, 'show'])->name('zonas-sociales.show');
    Route::get('/zonas-sociales/{id}/edit', [ZonaSocialWebController::class, 'edit'])->name('zonas-sociales.edit');
    Route::put('/zonas-sociales/{id}', [ZonaSocialWebController::class, 'update'])->name('zonas-sociales.update');
    Route::delete('/zonas-sociales/{id}', [ZonaSocialWebController::class, 'destroy'])->name('zonas-sociales.destroy');

    Route::get('/parqueaderos', [ParqueaderoWebController::class, 'index'])->name('parqueaderos.index');
    Route::get('/parqueaderos/create', [ParqueaderoWebController::class, 'create'])->name('parqueaderos.create');
    Route::post('/parqueaderos', [ParqueaderoWebController::class, 'store'])->name('parqueaderos.store');
    Route::get('/parqueaderos/{id}', [ParqueaderoWebController::class, 'show'])->name('parqueaderos.show');
    Route::get('/parqueaderos/{id}/edit', [ParqueaderoWebController::class, 'edit'])->name('parqueaderos.edit');
    Route::put('/parqueaderos/{id}', [ParqueaderoWebController::class, 'update'])->name('parqueaderos.update');
    Route::delete('/parqueaderos/{id}', [ParqueaderoWebController::class, 'destroy'])->name('parqueaderos.destroy');

    Route::prefix('politicas-precio-proyecto')->group(function () {
        Route::get('/', [PoliticaPrecioProyectoWebController::class, 'index'])->name('politicas-precio-proyecto.index');
        Route::get('/crear', [PoliticaPrecioProyectoWebController::class, 'create'])->name('politicas-precio-proyecto.create');
        Route::post('/', [PoliticaPrecioProyectoWebController::class, 'store'])->name('politicas-precio-proyecto.store');
        Route::get('/{id}', [PoliticaPrecioProyectoWebController::class, 'show'])->name('politicas-precio-proyecto.show');
        Route::get('/{id}/editar', [PoliticaPrecioProyectoWebController::class, 'edit'])->name('politicas-precio-proyecto.edit');
        Route::put('/{id}', [PoliticaPrecioProyectoWebController::class, 'update'])->name('politicas-precio-proyecto.update');
    });
});

// ============================================
// RUTAS WEB - MÓDULO DE VENTAS
// ============================================

// Catálogo (Módulo de Ventas)
Route::get('/catalogo', [CatalogoWebController::class, 'index'])->name('catalogo.index');
Route::get('/catalogo/{tipo}/{id}', [CatalogoWebController::class, 'show'])->name('catalogo.show');


// 1. Tipos de Cliente
Route::resource('tipos-cliente', TipoClienteWebController::class)->names([
    'index' => 'tipos-cliente.index',
    'create' => 'tipos-cliente.create',
    'store' => 'tipos-cliente.store',
    'show' => 'tipos-cliente.show',
    'edit' => 'tipos-cliente.edit',
    'update' => 'tipos-cliente.update',
    'destroy' => 'tipos-cliente.destroy',
]);

// 2. Tipos de Documento
Route::resource('tipos-documento', TipoDocumentoWebController::class)->names([
    'index' => 'tipos-documento.index',
    'create' => 'tipos-documento.create',
    'store' => 'tipos-documento.store',
    'show' => 'tipos-documento.show',
    'edit' => 'tipos-documento.edit',
    'update' => 'tipos-documento.update',
    'destroy' => 'tipos-documento.destroy',
]);

// 3. Clientes (usando documento como parámetro)
Route::get('clientes', [ClienteWebController::class, 'index'])->name('clientes.index');
Route::get('clientes/create', [ClienteWebController::class, 'create'])->name('clientes.create');
Route::post('clientes', [ClienteWebController::class, 'store'])->name('clientes.store');
Route::get('clientes/{documento}', [ClienteWebController::class, 'show'])->name('clientes.show');
Route::get('clientes/{documento}/edit', [ClienteWebController::class, 'edit'])->name('clientes.edit');
Route::put('clientes/{documento}', [ClienteWebController::class, 'update'])->name('clientes.update');
Route::delete('clientes/{documento}', [ClienteWebController::class, 'destroy'])->name('clientes.destroy');

// 4. Formas de Pago
Route::resource('formas-pago', FormaPagoWebController::class)->names([
    'index' => 'formas-pago.index',
    'create' => 'formas-pago.create',
    'store' => 'formas-pago.store',
    'show' => 'formas-pago.show',
    'edit' => 'formas-pago.edit',
    'update' => 'formas-pago.update',
    'destroy' => 'formas-pago.destroy',
]);

// 5. Estados de Venta
Route::resource('estados-venta', EstadoVentaWebController::class)->names([
    'index' => 'estados-venta.index',
    'create' => 'estados-venta.create',
    'store' => 'estados-venta.store',
    'show' => 'estados-venta.show',
    'edit' => 'estados-venta.edit',
    'update' => 'estados-venta.update',
    'destroy' => 'estados-venta.destroy',
]);

// 6. Conceptos de Pago
Route::resource('conceptos-pago', ConceptoPagoWebController::class)->names([
    'index' => 'conceptos-pago.index',
    'create' => 'conceptos-pago.create',
    'store' => 'conceptos-pago.store',
    'show' => 'conceptos-pago.show',
    'edit' => 'conceptos-pago.edit',
    'update' => 'conceptos-pago.update',
    'destroy' => 'conceptos-pago.destroy',
]);

// 7. Medios de Pago
Route::resource('medios-pago', MedioPagoWebController::class)->names([
    'index' => 'medios-pago.index',
    'create' => 'medios-pago.create',
    'store' => 'medios-pago.store',
    'show' => 'medios-pago.show',
    'edit' => 'medios-pago.edit',
    'update' => 'medios-pago.update',
    'destroy' => 'medios-pago.destroy',
]);

// 8. Ventas
Route::resource('ventas', VentaWebController::class)->names([
    'index' => 'ventas.index',
    'create' => 'ventas.create',
    'store' => 'ventas.store',
    'show' => 'ventas.show',
    'edit' => 'ventas.edit',
    'update' => 'ventas.update',
    'destroy' => 'ventas.destroy',
]);

Route::get('/ventas/inmuebles-disponibles', [VentaWebController::class, 'getInmueblesDisponibles'])
    ->name('ventas.inmuebles');

// 9. Planes de Amortización de Venta
Route::resource('planes-amortizacion-venta', PlanAmortizacionVentaWebController::class)->names([
    'index' => 'planes-amortizacion-venta.index',
    'create' => 'planes-amortizacion-venta.create',
    'store' => 'planes-amortizacion-venta.store',
    'show' => 'planes-amortizacion-venta.show',
    'edit' => 'planes-amortizacion-venta.edit',
    'update' => 'planes-amortizacion-venta.update',
    'destroy' => 'planes-amortizacion-venta.destroy',
]);

// 10. Pagos
Route::resource('pagos', PagoWebController::class)->names([
    'index' => 'pagos.index',
    'create' => 'pagos.create',
    'store' => 'pagos.store',
    'show' => 'pagos.show',
    'edit' => 'pagos.edit',
    'update' => 'pagos.update',
    'destroy' => 'pagos.destroy',
]);

// 11. Planes de Amortización - Cuotas
Route::resource('planes-amortizacion-cuota', PlanAmortizacionCuotaWebController::class)->names([
    'index' => 'planes-amortizacion-cuota.index',
    'create' => 'planes-amortizacion-cuota.create',
    'store' => 'planes-amortizacion-cuota.store',
    'show' => 'planes-amortizacion-cuota.show',
    'edit' => 'planes-amortizacion-cuota.edit',
    'update' => 'planes-amortizacion-cuota.update',
    'destroy' => 'planes-amortizacion-cuota.destroy',
]);


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
