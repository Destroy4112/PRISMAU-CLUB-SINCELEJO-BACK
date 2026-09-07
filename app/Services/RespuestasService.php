<?php

namespace App\Services;

use App\Models\Respuestas;
use Illuminate\Database\Eloquent\Collection;

class RespuestasService
{

   public function create(array $request): void
   {
      Respuestas::create($request);
   }

   public function getByPregunta(int $id): Collection
   {
      return Respuestas::where('pregunta_id', $id)->get();
   }

   public function update(array $request, int $id): void
   {
      $respuesta = Respuestas::findOrFail($id);
      $respuesta->update($request);
   }

   public function delete(int $id): void
   {
      $respuesta = Respuestas::findOrFail($id);
      $respuesta->delete();
   }
}
