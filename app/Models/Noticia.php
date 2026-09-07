<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Noticia extends Model
{
    use HasFactory;

    protected $fillable = [
        'Titulo',
        'Descripcion',
        'Imagen',
        'Vencimiento',
        'Destinatario',
        'Hora',
        'Tipo',
        'Correo',
        'Push',
        'Fecha'
    ];
}
