<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';
    protected $primaryKey = 'id_pago';

    protected $fillable = [
        'fecha',
        'id_venta',
        'referencia_pago',
        'id_concepto_pago',
        'id_medio_pago',
        'descripcion',
        'valor',
        'id_cuota',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    // Relaciones
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'id_venta', 'id_venta');
    }

    public function conceptoPago()
    {
        return $this->belongsTo(ConceptoPago::class, 'id_concepto_pago', 'id_concepto_pago');
    }

    public function medioPago()
    {
        return $this->belongsTo(MedioPago::class, 'id_medio_pago', 'id_medio_pago');
    }

    public function cuota()
    {
        return $this->belongsTo(PlanAmortizacionCuota::class, 'id_cuota', 'id_cuota');
    }
}