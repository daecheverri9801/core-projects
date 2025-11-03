<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PisoTorre extends Model
{
    use HasFactory;

    protected $table = 'pisos_torre';
    protected $primaryKey = 'id_piso_torre';

    protected $fillable = [
        'nivel',
        'id_torre',
        'uso'
    ];

    public function torre()
    {
        return $this->belongsTo(Torre::class, 'id_torre', 'id_torre');
    }

    public function apartamentos()
    {
        return $this->hasMany(Apartamento::class, 'id_piso_torre', 'id_piso_torre');
    }
}