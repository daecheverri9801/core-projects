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
        'id_empleado',
        'tipo_comision',
        'porcentaje',
        'vigente_desde',
        'vigente_hasta',
    ];

    protected $casts = [
        'vigente_desde' => 'date',
        'vigente_hasta' => 'date',
        'porcentaje' => 'decimal:3',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'id_proyecto', 'id_proyecto');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado', 'id_empleado');
    }
}
