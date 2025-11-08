<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    use HasFactory;

    protected $table = 'proyectos';
    protected $primaryKey = 'id_proyecto';

    protected $fillable = [
        'nombre',
        'descripcion',
        'fecha_inicio',
        'fecha_finalizacion',
        'presupuesto_inicial',
        'presupuesto_final',
        'metros_construidos',
        'cantidad_locales',
        'cantidad_apartamentos',
        'cantidad_parqueaderos_vehiculo',
        'cantidad_parqueaderos_moto',
        'estrato',
        'numero_pisos',
        'numero_torres',
        'porcentaje_cuota_inicial_min',
        'valor_min_separacion',
        'plazo_cuota_inicial_meses',
        'id_estado',
        'id_ubicacion',
        'prima_altura_base',
        'prima_altura_incremento',
        'prima_altura_activa',
    ];

    protected $casts = [
        'presupuesto_inicial' => 'decimal:2',
        'presupuesto_final' => 'decimal:2',
        'metros_construidos' => 'decimal:2',
        'porcentaje_cuota_inicial_min' => 'decimal:2',
        'valor_min_separacion' => 'decimal:2',
        'prima_altura_activa' => 'boolean',
    ];

    public function estado_proyecto()
    {
        return $this->belongsTo(Estado::class, 'id_estado', 'id_estado');
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class, 'id_ubicacion', 'id_ubicacion');
    }

    public function torres()
    {
        return $this->hasMany(Torre::class, 'id_proyecto', 'id_proyecto');
    }

    public function zonasSociales()
    {
        return $this->hasMany(ZonaSocial::class, 'id_proyecto', 'id_proyecto');
    }

    public function politicasPrecio()
    {
        return $this->hasMany(PoliticaPrecioProyecto::class, 'id_proyecto', 'id_proyecto');
    }

    public function politicasComisiones()
    {
        return $this->hasMany(PoliticaComision::class, 'id_proyecto', 'id_proyecto');
    }

    public function politicaVigente()
    {
        return $this->hasOne(PoliticaPrecioProyecto::class, 'id_proyecto', 'id_proyecto')
            ->where('estado', true)
            ->where(function ($query) {
                $query->whereNull('aplica_desde')
                    ->orWhere('aplica_desde', '<=', now());
            })
            ->latest('aplica_desde');
    }
}
