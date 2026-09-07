<?php

namespace App\Services;

use App\Models\Preguntas;
use Illuminate\Database\Eloquent\Collection;

class PreguntasService
{

   public function create(array $request): void
   {
      Preguntas::create($request);
   }

   public function getByEncuesta(int $id): Collection
   {
      return Preguntas::where('encuesta_id', $id)->get();
   }

   public function getPreguntasRespondidas(int $id): array
   {
      return Preguntas::whereIn('id', $id)->pluck('encuesta_id')->unique();
   }

   public function update(array $request, int $id): void
   {
      $Pregunta = Preguntas::findOrFail($id);
      $Pregunta->update($request);
   }

   public function delete(int $id): void
   {
      $Pregunta = Preguntas::findOrFail($id);
      $Pregunta->delete();
   }
}
