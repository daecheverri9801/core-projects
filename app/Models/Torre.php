<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Torre extends Model
{
    use HasFactory;

    protected $table = 'torres';
    protected $primaryKey = 'id_torre';

    protected $fillable = [
        'nombre_torre',
        'numero_pisos',
        'id_proyecto',
        'id_estado'
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'id_proyecto', 'id_proyecto');
    }

    public function pisos()
    {
        return $this->hasMany(PisoTorre::class, 'id_torre', 'id_torre');
    }

    public function apartamentos()
    {
        return $this->hasMany(Apartamento::class, 'id_torre', 'id_torre');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'id_estado', 'id_estado');
    }

    public function locales()
    {
        return $this->hasMany(Local::class, 'id_torre', 'id_torre');
    }
}
