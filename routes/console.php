<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('pricing:recalcular-proyectos')
    ->dailyAt('00:05')
    ->timezone('America/Bogota')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('ventas:caducar-separaciones')
    ->dailyAt('00:05')
    ->timezone('America/Bogota')
    ->withoutOverlapping()
    ->onOneServer();
