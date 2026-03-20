<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\VentaCreada;
use App\Listeners\EnviarNotificacionesVenta;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Dejar vacío para evitar duplicación con discovery.
     */
    protected $listen = [
        // \Illuminate\Auth\Events\Login::class => [
        //     \App\Listeners\LogSuccessfulLogin::class,
        // ],
        VentaCreada::class => [
            EnviarNotificacionesVenta::class,
        ],
    ];

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }

    public function boot(): void
    {
        //
    }
}
