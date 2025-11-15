<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';
    protected $primaryKey = 'documento';
    public $incrementing = false;

    protected $fillable = [
        'nombre',
        'id_tipo_cliente',
        'id_tipo_documento',
        'documento',
        'direccion',
        'telefono',
        'correo',
    ];

    // Relaciones
    public function tipoCliente()
    {
        return $this->belongsTo(TipoCliente::class, 'id_tipo_cliente', 'id_tipo_cliente');
    }

    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'id_tipo_documento', 'id_tipo_documento');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'documento_cliente', 'documento');
    }

    public function apartamentos()
    {
        return $this->hasMany(Apartamento::class, 'documento', 'documento');
    }
}
