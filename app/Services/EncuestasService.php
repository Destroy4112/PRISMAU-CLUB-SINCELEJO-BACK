<?php

namespace App\Services;

use App\Models\Encuestas;
use App\Models\RespuestasUsuario;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EncuestasService
{

   public function create(array $request): void
   {
      Encuestas::create([
         'Titulo'         => $request['Titulo'],
         'Descripcion'    => $request['Descripcion'],
         'Estado'         => 1,
      ]);
   }

   public function saveReplies(array $data, int $encuestaId): array
   {
      $encuesta = Encuestas::query()->where('Estado', 1)->with('preguntas.respuestas')->findOrFail($encuestaId);
      $userId = (int) $data['user_id'];

      $yaRespondida = RespuestasUsuario::query()->where('user_id', $userId)
         ->whereHas('pregunta', function ($query) use ($encuestaId) {
            $query->where('encuesta_id', $encuestaId);
         })->exists();

      if ($yaRespondida) {
         return [
            'status' => false,
            'message' => 'Esta encuesta ya fue respondida por el usuario.',
         ];
      }

      $respuestasEnviadas = collect($data['respuestas']);

      if ($respuestasEnviadas->pluck('pregunta_id')->duplicates()->isNotEmpty()) {
         return [
            'status' => false,
            'message' => 'No puede responder dos veces la misma pregunta.',
         ];
      }

      $preguntasEncuesta = $encuesta->preguntas->pluck('id')->sort()->values();
      $preguntasRespondidas = $respuestasEnviadas->pluck('pregunta_id')->map(fn($id) => (int) $id)->sort()->values();

      if ($preguntasEncuesta->toArray() !== $preguntasRespondidas->toArray()) {
         return [
            'status' => false,
            'message' => 'Debe responder todas las preguntas de la encuesta.',
         ];
      }

      foreach ($respuestasEnviadas as $item) {
         $pregunta = $encuesta->preguntas->firstWhere('id', (int) $item['pregunta_id']);

         $respuestaValida = $pregunta?->respuestas->contains('id', (int) $item['respuesta_id']);

         if (!$respuestaValida) {
            return [
               'status' => false,
               'message' => "La respuesta seleccionada no pertenece a la pregunta {$item['pregunta_id']}."
            ];
         }
      }

      DB::transaction(function () use ($respuestasEnviadas, $userId) {
         $registros = $respuestasEnviadas->map(fn($item) => [
            'user_id'      => $userId,
            'pregunta_id'  => (int) $item['pregunta_id'],
            'respuesta_id' => (int) $item['respuesta_id'],
            'created_at'    => now(),
            'updated_at'    => now(),
         ])->all();
         RespuestasUsuario::insert($registros);
      });

      return [
         'status' => true,
         'message' => 'Respuestas guardadas correctamente',
      ];
   }

   public function getAll(): Collection
   {
      return Encuestas::withCount('preguntas')->orderBy('created_at', 'desc')->get();
   }

   public function usersReply(int $encuestaId): Collection
   {
      $respuestas = RespuestasUsuario::query()
         ->with(['user.asociado', 'user.adherente', 'pregunta', 'respuesta'])
         ->whereHas('pregunta', function ($query) use ($encuestaId) {
            $query->where('encuesta_id', $encuestaId);
         })->orderBy('user_id')->orderBy('pregunta_id')->get();

      $personas = $respuestas
         ->groupBy('user_id')
         ->map(function ($items) {
            $first = $items->first();
            $user = $first->user;
            $asociado = $user?->asociado;
            $adherente = $user?->adherente;
            $persona = $asociado ?? $adherente;

            return [
               'user_id' => $user?->id,
               'tipo_persona' => $asociado ? 'ASOCIADO' : 'ADHERENTE',
               'persona_id' => $persona?->id,
               'nombre' => $persona?->Nombre . ' ' . $persona?->Apellidos ?? null,
               'documento' => $persona?->Documento ?? $persona?->documento ?? null,
               'correo' => $persona?->Correo ?? $persona?->correo ?? null,
               'telefono' => $persona?->Telefono ?? $persona?->telefono ?? null,
               'fecha_respuesta' => $items->max('created_at'),
               'respuestas' => $items->map(function ($item) {
                  return [
                     'pregunta_id' => $item->pregunta_id,
                     'pregunta' => $item->pregunta?->Pregunta,
                     'respuesta_id' => $item->respuesta_id,
                     'respuesta' => $item->respuesta?->Respuesta,
                  ];
               })->values(),
            ];
         })
         ->values();

      return $personas;
   }

   public function getAvailable(int $userId): Collection
   {
      return Encuestas::query()
         ->where('Estado', 1)
         ->whereHas('preguntas')
         ->whereDoesntHave('preguntas.respuestasUsuarios', function ($query) use ($userId) {
            $query->where('user_id', $userId);
         })
         ->withCount('preguntas')
         ->latest()
         ->get();
   }

   public function get(int $id): ?Encuestas
   {
      return Encuestas::with(['preguntas.respuestas'])->find($id);
   }

   public function update(array $request, int $id): void
   {
      $encuesta = Encuestas::findOrFail($id);
      $encuesta->update($request);
   }

   public function delete(int $id): void
   {
      $encuesta = Encuestas::findOrFail($id);
      $encuesta->delete();
   }
}
