<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Middleware global para el grupo web
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Middleware global para el grupo api (si tienes)
        $middleware->api(append: [
            // Agrega middlewares globales para API si los tienes
        ]);

        // Registrar alias de middleware
        $middleware->alias([
            'check.cargo' => \App\Http\Middleware\CheckCargo::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->withSchedule(function (Schedule $schedule) {
        // ✅ AQUÍ VA TU CÓDIGO DE SCHEDULING
        $schedule->command('ventas:caducar-separaciones')->dailyAt('02:00');
    })
    ->create();
