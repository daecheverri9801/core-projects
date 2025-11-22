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
        'id_proyecto',
        'nombre',
        'area_construida',
        'area_privada',
        'cantidad_habitaciones',
        'cantidad_banos',
        'valor_m2',
        'valor_estimado',
        'imagen',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'id_proyecto', 'id_proyecto');
    }
    public function apartamentos()
    {
        return $this->hasMany(Apartamento::class, 'id_tipo_apartamento', 'id_tipo_apartamento');
    }
}
