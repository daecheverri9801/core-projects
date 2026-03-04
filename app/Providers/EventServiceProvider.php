<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Dejar vacío para evitar duplicación con discovery.
     */
    protected $listen = [
        // \Illuminate\Auth\Events\Login::class => [
        //     \App\Listeners\LogSuccessfulLogin::class,
        // ],
    ];

    public function boot(): void
    {
        //
    }
}
