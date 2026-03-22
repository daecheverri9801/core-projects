<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Apartamento;
use App\Observers\ApartamentoObserver;
use App\Models\PoliticaPrecioProyecto;
use App\Observers\PoliticaPrecioProyectoObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Apartamento::observe(ApartamentoObserver::class);
        PoliticaPrecioProyecto::observe(PoliticaPrecioProyectoObserver::class);
    }
}
