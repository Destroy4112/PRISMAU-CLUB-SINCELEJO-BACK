<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RespuestasUsuario extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'pregunta_id', 'respuesta_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pregunta()
    {
        return $this->belongsTo(Preguntas::class, 'pregunta_id');
    }

    public function respuesta()
    {
        return $this->belongsTo(Respuestas::class, 'respuesta_id');
    }
}
