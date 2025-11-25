<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meta extends Model
{
    protected $table = 'metas';
    protected $primaryKey = 'id_meta';

    protected $fillable = [
        'id_proyecto',
        'id_empleado',
        'tipo',
        'mes',
        'ano',
        'meta_valor',
        'meta_unidades',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'id_proyecto');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado');
    }
}
