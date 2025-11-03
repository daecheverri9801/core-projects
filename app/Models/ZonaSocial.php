<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZonaSocial extends Model
{
    use HasFactory;

    protected $table = 'zonas_sociales';
    protected $primaryKey = 'id_zona_social';

    protected $fillable = [
        'nombre',
        'descripcion',
        'id_proyecto'
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'id_proyecto', 'id_proyecto');
    }
}