<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedioPago extends Model
{
    use HasFactory;

    protected $table = 'medios_pago';
    protected $primaryKey = 'id_medio_pago';

    protected $fillable = [
        'medio_pago',
        'descripcion',
    ];

    // Relaciones
    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_medio_pago', 'id_medio_pago');
    }
}
