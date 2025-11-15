<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanAmortizacionCuota extends Model
{
    use HasFactory;

    protected $table = 'planes_amortizacion_cuota';
    protected $primaryKey = 'id_cuota';

    protected $fillable = [
        'id_plan',
        'numero_cuota',
        'fecha_vencimiento',
        'valor_cuota',
        'valor_interes',
        'valor_capital',
        'saldo',
        'estado',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
    ];

    // Relaciones
    public function planAmortizacion()
    {
        return $this->belongsTo(PlanAmortizacionVenta::class, 'id_plan', 'id_plan');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_cuota', 'id_cuota');
    }
}