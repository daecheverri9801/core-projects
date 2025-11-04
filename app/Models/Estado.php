<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    use HasFactory;

    protected $table = 'estados';
    protected $primaryKey = 'id_estado';

    protected $fillable = [
        'nombre',
        'descripcion'
    ];

    public function proyectos()
    {
        return $this->hasMany(Proyecto::class, 'id_estado', 'id_estado');
    }
    public function torres()
    {
        return $this->hasMany(Torre::class, 'id_estado', 'id_estado');
    }
}