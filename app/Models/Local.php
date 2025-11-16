<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Local extends Model
{
    use HasFactory;

    protected $table = 'locales';
    protected $primaryKey = 'id_local';

    protected $fillable = [
        'numero',
        'id_estado_inmueble',
        'area_total_local',
        'id_torre',
        'id_piso_torre',
        'valor_m2',
        'valor_total'
    ];

    protected $casts = [
        'area_total_local' => 'decimal:2',
        'valor_m2' => 'decimal:2',
        'valor_total' => 'decimal:2'
    ];

    public function estadoInmueble()
    {
        return $this->belongsTo(EstadoInmueble::class, 'id_estado_inmueble', 'id_estado_inmueble');
    }

    public function torre()
    {
        return $this->belongsTo(Torre::class, 'id_torre', 'id_torre');
    }

    public function pisoTorre()
    {
        return $this->belongsTo(PisoTorre::class, 'id_piso_torre', 'id_piso_torre');
    }

    // ✅ NUEVA: Relación inversa con ventas
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'id_local');
    }

    public function getValorComercialAttribute()
    {
        return $this->valor_total ?? 0;
    }
}
