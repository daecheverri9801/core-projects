<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanAmortizacionVenta extends Model
{
    use HasFactory;

    protected $table = 'planes_amortizacion_venta';
    protected $primaryKey = 'id_plan';

    protected $fillable = [
        'id_venta',
        'tipo_plan',
        'valor_interes_anual',
        'plazo_meses',
        'fecha_inicio',
        'observacion',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
    ];

    // Relaciones
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'id_venta', 'id_venta');
    }

    public function cuotas()
    {
        return $this->hasMany(PlanAmortizacionCuota::class, 'id_plan', 'id_plan');
    }
}