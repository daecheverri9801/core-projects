<?php

namespace App\Observers;

use App\Models\Apartamento;
use App\Models\EstadoInmueble;
use App\Services\PriceEngine;

class ApartamentoObserver
{
    public function updated(Apartamento $apartamento): void
    {
        if (!$apartamento->wasChanged('id_estado_inmueble')) {
            return;
        }

        $estadoDisponibleId = EstadoInmueble::where('nombre', 'Disponible')
            ->value('id_estado_inmueble');

        if ((int) $apartamento->id_estado_inmueble !== (int) $estadoDisponibleId) {
            return;
        }

        app(PriceEngine::class)->recalcularApartamentoSegunPoliticasActivas($apartamento->fresh());
    }
}
