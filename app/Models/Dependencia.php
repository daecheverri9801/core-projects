<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dependencia extends Model
{
    use HasFactory;

    protected $table = 'dependencias';
    protected $primaryKey = 'id_dependencia';

    protected $fillable = [
        'nombre',
        'descripcion'
    ];

    public function empleados()
    {
        return $this->hasMany(Empleado::class, 'id_dependencia', 'id_dependencia');
    }
}