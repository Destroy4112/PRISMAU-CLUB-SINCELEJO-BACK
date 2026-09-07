<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisponibilidadEspacio extends Model
{
    use HasFactory;

    protected $fillable = [
        'espacio_id',
        'Dia',
        'Inicio',
        'Fin',
    ];

    public function espacio()
    {
        return $this->belongsTo(Espacio::class);
    }
}
