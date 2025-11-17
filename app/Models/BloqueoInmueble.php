<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BloqueoInmueble extends Model
{
    protected $table = 'bloqueos_inmuebles';
    protected $primaryKey = 'id_bloqueo';

    protected $fillable = [
        'id_inmueble',
        'inmueble_tipo',
        'id_empleado',
        'bloqueado_en',
        'expires_at',
        'released_at',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado', 'id_empleado');
    }
}
