<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';
    protected $primaryKey = 'id_venta';

    protected $fillable = [
        'id_empleado',
        'documento_cliente',
        'fecha_venta',
        'fecha_vencimiento',
        'id_apartamento',
        'id_local',
        'id_proyecto',
        'id_forma_pago',
        'id_estado_inmueble',
        'cuota_inicial',
        'valor_restante',
        'descripcion',
        'valor_base',
        'iva',
        'valor_total',
    ];

    protected $casts = [
        'fecha_venta' => 'date',
        'fecha_vencimiento' => 'date',
    ];

    // Relaciones
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado', 'id_empleado');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'documento_cliente', 'documento');
    }

    public function apartamento()
    {
        return $this->belongsTo(Apartamento::class, 'id_apartamento', 'id_apartamento');
    }

    public function local()
    {
        return $this->belongsTo(Local::class, 'id_local', 'id_local');
    }

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'id_proyecto', 'id_proyecto');
    }

    public function formaPago()
    {
        return $this->belongsTo(FormaPago::class, 'id_forma_pago', 'id_forma_pago');
    }

    public function estadoInmueble()
    {
        return $this->belongsTo(EstadoInmueble::class, 'id_estado_inmueble', 'id_estado_inmueble');
    }

    public function planAmortizacion()
    {
        return $this->hasOne(PlanAmortizacionVenta::class, 'id_venta', 'id_venta');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_venta', 'id_venta');
    }

    // ✅ NUEVA: Relación polimórfica para obtener el inmueble (apartamento o local)
    public function inmueble()
    {
        if ($this->id_apartamento) {
            return $this->apartamento();
        }
        if ($this->id_local) {
            return $this->local();
        }
        return null;
    }

    // ✅ NUEVA: Accessor para obtener el estado del inmueble
    public function getEstadoInmuebleAttribute()
    {
        if ($this->apartamento) {
            return $this->apartamento->estadoInmueble;
        }
        if ($this->local) {
            return $this->local->estadoInmueble;
        }
        return null;
    }
}
