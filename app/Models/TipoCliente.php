<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoCliente extends Model
{
    use HasFactory;

    protected $table = 'tipos_cliente';
    protected $primaryKey = 'id_tipo_cliente';

    protected $fillable = [
        'tipo_cliente',
        'descripcion',
    ];

    // Relaciones
    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'id_tipo_cliente', 'id_tipo_cliente');
    }
}