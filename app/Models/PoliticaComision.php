<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoliticaComision extends Model
{
    use HasFactory;

    protected $table = 'politicas_comision';
    protected $primaryKey = 'id_politica_comision';

    protected $fillable = [
        'id_proyecto',
        'aplica_a',
        'base_calculo',
        'porcentaje',
        'valor_fijo',
        'minimo_venta_estado',
        'descripcion',
        'vigente_desde',
        'vigente_hasta'
    ];

    protected $casts = [
        'vigente_desde' => 'date',
        'vigente_hasta' => 'date',
        'porcentaje' => 'decimal:3',
        'valor_fijo' => 'decimal:2'
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'id_proyecto', 'id_proyecto');
    }
}
