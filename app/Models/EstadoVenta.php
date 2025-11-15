<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadoVenta extends Model
{
    use HasFactory;

    protected $table = 'estados_venta';
    protected $primaryKey = 'id_estado_venta';

    protected $fillable = [
        'estado_venta',
        'descripcion',
    ];

    // Relaciones
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'id_estado_venta', 'id_estado_venta');
    }
}
