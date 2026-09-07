<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adherente extends Model
{
    use HasFactory;

    protected $fillable = [
        'imagen',
        'user_id',
        'asociado_id',
        'Nombre',
        'Apellidos',
        'Correo',
        'Telefono',
        'FechaNacimiento',
        'LugarNacimiento',
        'TipoDocumento',
        'Documento',
        'Sexo',
        'Codigo',
        'DireccionResidencia',
        'CiudadResidencia',
        'TiempoResidencia',
        'EstadoCivil',
        'Profesion',
        'Trabajo',
        'Cargo',
        'TiempoServicio',
        'TelOficina',
        'DireccionOficina',
        'CiudadOficina',
        'Estado',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function familiares()
    {
        return $this->hasMany(Familiar::class);
    }

    public function asociado()
    {
        return $this->hasOne(Asociado::class);
    }

    public function invitados()
    {
        return $this->hasMany(Invitado::class, 'usuario_que_invita_id');
    }

    public function estados()
    {
        return $this->hasMany(Estados::class);
    }
}
