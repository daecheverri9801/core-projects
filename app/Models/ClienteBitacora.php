<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClienteBitacora extends Model
{
    use HasFactory;

    protected $table = 'cliente_bitacoras';
    protected $primaryKey = 'id_bitacora_cliente';

    protected $fillable = [
        'documento_cliente',
        'id_empleado',
        'fecha',
        'comentario',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'documento_cliente', 'documento');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado', 'id_empleado');
    }
}
