<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Espacio extends Model
{
    use HasFactory;

    protected $fillable = [
        'imagen',
        'Descripcion',
        'Estado',
    ];

    public function disponibilidades()
    {
        return $this->hasMany(DisponibilidadEspacio::class);
    }

    public function reservas()
    {
        return $this->hasMany(Reservas::class);
    }
}
