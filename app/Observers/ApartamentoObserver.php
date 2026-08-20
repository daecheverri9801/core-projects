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

        /*
         * ========================================================
         * 1. Si cambió a Disponible, recalcular precio
         * ========================================================
         */
        if ($apartamento->wasChanged('id_estado_inmueble')) {
            $estadoDisponibleId = EstadoInmueble::where(
                'nombre',
                'Disponible'
            )->value('id_estado_inmueble');

            if (
                (int) $apartamento->id_estado_inmueble
                ===
                (int) $estadoDisponibleId
            ) {
                $priceEngine
                    ->recalcularApartamentoSegunPoliticasActivas(
                        $apartamento->fresh()
                    );

                return;
            }
        }

        /*
         * ========================================================
         * 2. Cambios que afectan el precio del apartamento
         * ========================================================
         *
         * prima_altura NO se observa porque ahora es un campo
         * derivado del tipo + piso.
         */
        $camposQueAfectanPrecio = [
            'id_tipo_apartamento',
            'id_piso_torre',
            'id_torre',
            'valor_total',
        ];

        foreach ($camposQueAfectanPrecio as $campo) {
            if ($apartamento->wasChanged($campo)) {
                $priceEngine
                    ->recalcularApartamentoSegunPoliticasActivas(
                        $apartamento->fresh()
                    );

                return;
            }
        }
    }
}
