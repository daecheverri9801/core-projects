<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parqueadero extends Model
{
    use HasFactory;

    protected $table = 'parqueaderos';
    protected $primaryKey = 'id_parqueadero';

    protected $fillable = [
        'numero',
        'tipo',
        'id_apartamento'
    ];

    public function apartamento()
    {
        return $this->belongsTo(Apartamento::class, 'id_apartamento', 'id_apartamento');
    }

}