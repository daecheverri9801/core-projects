<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanPagoProyecto extends Model
{
    use HasFactory;

    protected $table = 'planes_pago_proyecto';
    protected $primaryKey = 'id_plan_pago_proyecto';

    protected $fillable = [
        'id_proyecto',
        'codigo',
        'nombre',
        'descripcion',
        'orden',
        'tipo_plan',
        'valor_separacion',
        'porcentaje_cuota_inicial',
        'plazo_cuota_inicial_meses',
        'frecuencia_cuota_inicial_meses',
        'plazo_pago_total_dias',
        'porcentaje_escritura',
        'tipo_descuento',
        'valor_descuento',
        'base_descuento',
        'beneficio_comercial',
        'permite_plazo_manual',
        'permite_cuotas_manuales',
        'activo',
    ];

    protected $casts = [
        'valor_separacion' => 'decimal:2',
        'porcentaje_cuota_inicial' => 'decimal:4',
        'porcentaje_escritura' => 'decimal:4',
        'valor_descuento' => 'decimal:4',
        'permite_plazo_manual' => 'boolean',
        'permite_cuotas_manuales' => 'boolean',
        'activo' => 'boolean',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'id_proyecto', 'id_proyecto');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('orden')->orderBy('id_plan_pago_proyecto');
    }
}
