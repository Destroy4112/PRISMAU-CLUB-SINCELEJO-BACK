<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Familiar extends Model
{
    use HasFactory;

    protected $fillable = [
        'imagen',
        'user_id',
        'asociado_id',
        'adherente_id',
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
        'EstadoCivil',
        'Cargo',
        'Parentesco',
        'Estado',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function asociado()
    {
        return $this->belongsTo(Asociado::class);
    }

    public function adherente()
    {
        return $this->belongsTo(Adherente::class);
    }
}
