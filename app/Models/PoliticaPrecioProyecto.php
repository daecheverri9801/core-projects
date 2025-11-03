<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoliticaPrecioProyecto extends Model
{
    use HasFactory;

    protected $table = 'politicas_precio_proyecto';
    protected $primaryKey = 'id_politica_precio';

    protected $fillable = [
        'id_proyecto',
        'ventas_por_escalon',
        'porcentaje_aumento',
        'aplica_desde',
        'estado'
    ];

    protected $casts = [
        'estado' => 'boolean',
        'aplica_desde' => 'date',
        'porcentaje_aumento' => 'decimal:3'
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'id_proyecto', 'id_proyecto');
    }
}