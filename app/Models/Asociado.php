<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asociado extends Model
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
        return $this->hasMany(Familiar::class, 'asociado_id');
    }

    public function adherente()
    {
        return $this->belongsTo(Adherente::class);
    }

    public function invitados()
    {
        return $this->hasMany(Invitado::class);
    }
    
    public function estados()
    {
        return $this->hasMany(Estados::class);
    }
}
