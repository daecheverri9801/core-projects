<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    protected $table = 'login_logs';

    protected $fillable = [
        'id_empleado',
        'guard_name',
        'ip',
        'user_agent',
        'logged_in_at',
    ];

    protected $casts = [
        'logged_in_at' => 'datetime',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado', 'id_empleado');
    }
}
