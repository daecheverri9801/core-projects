<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FormaPago;

class FormaPagoSeeder extends Seeder
{
    public function run()
    {
        $formas = [
            ['forma_pago' => 'Contado', 'descripcion' => 'Pago total al momento de la compra.'],
            ['forma_pago' => 'Crédito Hipotecario', 'descripcion' => 'Financiación a través de una entidad bancaria.'],
            ['forma_pago' => 'Crédito Directo', 'descripcion' => 'Financiación otorgada por la constructora.'],
            ['forma_pago' => 'Leasing Habitacional', 'descripcion' => 'Arrendamiento con opción de compra.'],
            ['forma_pago' => 'Permuta', 'descripcion' => 'Intercambio del inmueble por otro bien.'],
            ['forma_pago' => 'Mixta', 'descripcion' => 'Combinación de varias formas de pago.'],
        ];

        foreach ($formas as $forma) {
            FormaPago::firstOrCreate($forma);
        }
    }
}
