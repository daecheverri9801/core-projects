<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProyectoMetaComercial extends Model
{
    protected $table = 'proyecto_metas_comerciales';
    protected $primaryKey = 'id_meta';

    protected $fillable = [
        'id_proyecto',
        'mes_anio',
        'meta_unidades',
        'meta_valor',
        'meta_recaudos',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'id_proyecto', 'id_proyecto');
    }
}
