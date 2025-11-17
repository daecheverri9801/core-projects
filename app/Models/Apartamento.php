<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Apartamento extends Model
{
    use HasFactory;

    protected $table = 'apartamentos';
    protected $primaryKey = 'id_apartamento';

    protected $fillable = [
        'numero',
        'id_tipo_apartamento',
        'id_torre',
        'id_piso_torre',
        'id_estado_inmueble',
        'valor_total',
        'prima_altura',
        'valor_politica',
        'valor_final',
        'documento',
    ];

    protected $casts = [
        'valor_total' => 'decimal:2',
        'valor_politica' => 'decimal:2',
        'valor_final' => 'decimal:2',
    ];

    public function tipoApartamento()
    {
        return $this->belongsTo(TipoApartamento::class, 'id_tipo_apartamento', 'id_tipo_apartamento');
    }

    public function torre()
    {
        return $this->belongsTo(Torre::class, 'id_torre', 'id_torre');
    }

    public function pisoTorre()
    {
        return $this->belongsTo(PisoTorre::class, 'id_piso_torre', 'id_piso_torre');
    }

    public function estadoInmueble()
    {
        return $this->belongsTo(EstadoInmueble::class, 'id_estado_inmueble', 'id_estado_inmueble');
    }

    public function parqueaderos()
    {
        return $this->hasMany(Parqueadero::class, 'id_apartamento', 'id_apartamento');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'documento', 'documento');
    }

    // ✅ NUEVA: Relación inversa con ventas
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'id_apartamento');
    }

    public function getValorComercialAttribute()
    {
        $base = $this->valor_total ?? 0;
        $prima = $this->prima_altura ?? 0;
        $politica = $this->valor_politica ?? 0;

        return $base + $prima + $politica;
    }

    public function estaCongelado()
    {
        return $this->id_estado_inmueble &&
            $this->estadoInmueble &&
            strtolower($this->estadoInmueble->nombre) === 'congelado';
    }
}
