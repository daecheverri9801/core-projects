<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class HealthcheckController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('Health/Index', [
            'phpVersion' => PHP_VERSION,
            'appEnv' => config('app.env'),
            'database' => \DB::connection()->getDatabaseName(),
        ]);
    }
}