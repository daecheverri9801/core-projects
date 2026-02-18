<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class Empleado extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'empleados';
    protected $primaryKey = 'id_empleado';

    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'telefono',
        'id_cargo',
        'id_dependencia',
        'estado',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token', // si usas este campo
    ];

    protected $casts = [
        'estado' => 'boolean',
        'password' => 'hashed', 
    ];

    // protected static function booted()
    // {
    //     static::saving(function ($empleado) {
    //         if ($empleado->isDirty('password')) {
    //             $empleado->password = Hash::make($empleado->password);
    //         }
    //     });
    // }

    // Si usas mutator para encriptar password automáticamente
    // public function setPasswordAttribute($password)
    // {
    //     $this->attributes['password'] = bcrypt($password);
    // }

    public function cargo()
    {
        return $this->belongsTo(Cargo::class, 'id_cargo', 'id_cargo');
    }

    public function dependencia()
    {
        return $this->belongsTo(Dependencia::class, 'id_dependencia', 'id_dependencia');
    }
}
