<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Proyecto;
use App\Services\PriceEngine;

class RecalcularPreciosProyectos extends Command
{
    protected $signature = 'pricing:recalcular-proyectos {--proyecto_id=}';
    protected $description = 'Recalcula precios de inmuebles por politicas (ventas o fecha)';

    public function handle(PriceEngine $engine): int
    {
        $proyectoId = $this->option('proyecto_id');

        $query = Proyecto::query();

        if ($proyectoId) {
            $query->where('id_proyecto', (int)$proyectoId);
        }

        $proyectos = $query
            ->whereHas('politicasPrecio', function ($q) {
                $q->where('estado', true)
                    ->whereNotNull('aplica_desde')
                    ->whereDate('aplica_desde', '<=', now()->toDateString());
            })
            ->get();


        $count = 0;
        foreach ($proyectos as $p) {
            $engine->recalcularProyecto($p);
            $count++;
        }

        $this->info("Recalculo ejecutado para {$count} proyecto(s).");
        return Command::SUCCESS;
    }
}
