<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Respuestas extends Model
{
   use HasFactory;

   protected $fillable = ['pregunta_id', 'Respuesta'];

   public function pregunta()
   {
      return $this->belongsTo(Preguntas::class);
   }
}
