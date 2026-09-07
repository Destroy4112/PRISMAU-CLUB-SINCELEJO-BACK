<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Encuestas extends Model
{
    use HasFactory;

    protected $fillable = ['Titulo', 'Descripcion', 'Estado'];

    public function preguntas()
    {
        return $this->hasMany(Preguntas::class, 'encuesta_id');
    }
    
}
