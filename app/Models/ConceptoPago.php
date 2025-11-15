<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConceptoPago extends Model
{
    use HasFactory;

    protected $table = 'conceptos_pago';
    protected $primaryKey = 'id_concepto_pago';

    protected $fillable = [
        'concepto',
        'descripcion',
    ];

    // Relaciones
    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_concepto_pago', 'id_concepto_pago');
    }
}
