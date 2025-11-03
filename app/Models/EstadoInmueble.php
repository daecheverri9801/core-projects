<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadoInmueble extends Model
{
    use HasFactory;

    protected $table = 'estados_inmueble';
    protected $primaryKey = 'id_estado_inmueble';

    protected $fillable = [
        'nombre',
        'descripcion'
    ];

    public function apartamentos()
    {
        return $this->hasMany(Apartamento::class, 'id_estado_inmueble', 'id_estado_inmueble');
    }

    public function locales()
    {
        return $this->hasMany(Local::class, 'id_estado_inmueble', 'id_estado_inmueble');
    }
}