<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ciudad extends Model
{
    use HasFactory;

    protected $table = 'ciudades';
    protected $primaryKey = 'id_ciudad';

    protected $fillable = [
        'nombre',
        'codigo_postal',
        'id_departamento'
    ];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }

    public function ubicaciones()
    {
        return $this->hasMany(Ubicacion::class, 'id_ciudad', 'id_ciudad');
    }
}