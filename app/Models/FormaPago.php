<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormaPago extends Model
{
    use HasFactory;

    protected $table = 'formas_pago';
    protected $primaryKey = 'id_forma_pago';

    protected $fillable = [
        'forma_pago',
        'descripcion',
    ];

    // Relaciones
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'id_forma_pago', 'id_forma_pago');
    }
}
