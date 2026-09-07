<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    use HasFactory;

    protected $fillable = [
        'imagen',
        'user_id',
        'Nombre',
        'Apellidos',
        'Correo',
        'Telefono',
        'FechaNacimiento',
        'LugarNacimiento',
        'TipoDocumento',
        'Documento',
        'Sexo',
        'DireccionResidencia',
        'CiudadResidencia',
        'EstadoCivil',
        'Cargo',
        'Estado',
    ];

    protected $appends = ['Rol'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getRolAttribute()
    {
        return $this->user?->Rol;
    }
}
