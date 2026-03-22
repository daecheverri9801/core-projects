<?php

namespace App\Observers;

use App\Models\PoliticaPrecioProyecto;
use App\Models\Proyecto;
use App\Services\PriceEngine;

class PoliticaPrecioProyectoObserver
{
    public function created(PoliticaPrecioProyecto $politica): void
    {
        $this->recalcularProyecto($politica->id_proyecto);
    }

    public function updated(PoliticaPrecioProyecto $politica): void
    {
        $camposQueAfectanPrecio = [
            'id_proyecto',
            'ventas_por_escalon',
            'porcentaje_aumento',
            'aplica_desde',
            'estado',
        ];

        $huboCambioRelevante = false;

        foreach ($camposQueAfectanPrecio as $campo) {
            if ($politica->wasChanged($campo)) {
                $huboCambioRelevante = true;
                break;
            }
        }

        if (!$huboCambioRelevante) {
            return;
        }

        // Si cambió de proyecto, recalcular ambos
        $idProyectoAnterior = $politica->getOriginal('id_proyecto');
        $idProyectoNuevo = $politica->id_proyecto;

        if ($idProyectoAnterior && (int) $idProyectoAnterior !== (int) $idProyectoNuevo) {
            $this->recalcularProyecto((int) $idProyectoAnterior);
        }

        if ($idProyectoNuevo) {
            $this->recalcularProyecto((int) $idProyectoNuevo);
        }
    }

    public function deleted(PoliticaPrecioProyecto $politica): void
    {
        $this->recalcularProyecto($politica->id_proyecto);
    }

    protected function recalcularProyecto(?int $idProyecto): void
    {
        if (!$idProyecto) {
            return;
        }

        $proyecto = Proyecto::find($idProyecto);

        if (!$proyecto) {
            return;
        }

        app(PriceEngine::class)->recalcularProyecto($proyecto);
    }
}
