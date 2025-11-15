<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoDocumento extends Model
{
    use HasFactory;

    protected $table = 'tipos_documento';
    protected $primaryKey = 'id_tipo_documento';

    protected $fillable = [
        'tipo_documento',
        'descripcion',
    ];

    // Relaciones
    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'id_tipo_documento', 'id_tipo_documento');
    }
}