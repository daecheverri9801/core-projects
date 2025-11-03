<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoApartamento extends Model
{
    use HasFactory;

    protected $table = 'tipos_apartamento';
    protected $primaryKey = 'id_tipo_apartamento';

    protected $fillable = [
        'nombre',
        'area_construida',
        'area_privada',
        'cantidad_habitaciones',
        'cantidad_banos',
        'valor_m2'
    ];

    public function apartamentos()
    {
        return $this->hasMany(Apartamento::class, 'id_tipo_apartamento', 'id_tipo_apartamento');
    }
}