<?php

namespace App\Observers;

use App\Models\Apartamento;
use App\Models\EstadoInmueble;
use App\Services\PriceEngine;

class ApartamentoObserver
{
    public function updated(Apartamento $apartamento): void
    {
        $priceEngine = app(PriceEngine::class);

        // 1) Si cambió a Disponible, recalcular según políticas activas
        if ($apartamento->wasChanged('id_estado_inmueble')) {
            $estadoDisponibleId = EstadoInmueble::where('nombre', 'Disponible')
                ->value('id_estado_inmueble');

            if ((int) $apartamento->id_estado_inmueble === (int) $estadoDisponibleId) {
                $priceEngine->recalcularApartamentoSegunPoliticasActivas($apartamento->fresh());
                return;
            }
        }

        // 2) Si cambió el tipo de apartamento, recalcular de inmediato
        if (
            $apartamento->wasChanged('id_tipo_apartamento') ||
            $apartamento->wasChanged('valor_total') ||
            $apartamento->wasChanged('prima_altura')
        ) {
            $priceEngine->recalcularApartamentoSegunPoliticasActivas($apartamento->fresh());
            return;
        }
    }
}
