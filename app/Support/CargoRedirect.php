<?php

namespace App\Support;

class CargoRedirect
{
    public static function rutaInicial(string $cargoNombre): string
    {
        return match ($cargoNombre) {
            'Gerente'        => route('gerencia.dashboard'),
            'Directora Comercial' => route('catalogo.index'),
            'Asesora Comercial'   => route('catalogo.index'),
            'Administrador' => route('dashboard'),
            default          => route('home'),
        };
    }
}
