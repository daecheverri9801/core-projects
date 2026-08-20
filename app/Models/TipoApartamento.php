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

        // Prima de altura por tipo
        'prima_altura_activa',
        'nivel_inicio_prima',
        'prima_altura_base',
        'prima_altura_incremento',
    ];

    protected $casts = [
        'area_construida' => 'decimal:2',
        'area_privada' => 'decimal:2',
        'valor_m2' => 'decimal:2',
        'valor_estimado' => 'decimal:2',

        'prima_altura_activa' => 'boolean',
        'nivel_inicio_prima' => 'integer',
        'prima_altura_base' => 'decimal:2',
        'prima_altura_incremento' => 'decimal:2',
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
