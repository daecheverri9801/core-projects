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
        'id_apartamento',
        'id_torre',
        'id_proyecto',
        'precio'
    ];

    protected $casts = [
        'precio' => 'decimal:2',
    ];

    public function apartamento()
    {
        return $this->belongsTo(Apartamento::class, 'id_apartamento', 'id_apartamento');
    }

    public function torre()
    {
        return $this->belongsTo(Torre::class, 'id_torre', 'id_torre');
    }

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'id_proyecto', 'id_proyecto');
    }
}
