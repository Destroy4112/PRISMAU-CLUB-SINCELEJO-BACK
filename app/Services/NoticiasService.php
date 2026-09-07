<?php

namespace App\Services;

use App\Models\Noticia;
use Illuminate\Support\Collection;

class NoticiasService
{

   public function create(array $data): void
   {
      $rolId = (int) $data['Destinatario'];
      Noticia::create([
         "Titulo" => $data['Titulo'],
         "Descripcion" => $data['Descripcion'],
         "Vencimiento" => $data['Vencimiento'],
         "Fecha" => $data['Fecha'],
         "Hora" => $data['Hora'],
         "Tipo" => $data['Tipo'],
         "Correo" => $data['Correo'],
         "Push" => $data['Push'],
         "Destinatario" => $rolId,
      ]);

      // $usuarios = User::where('Rol', $rolId)->with($relacion)->get();

      // if (filter_var($request->correo, FILTER_VALIDATE_BOOLEAN)) {
      //     foreach ($usuarios as $user) {
      //         $perfil = $user->{$relacion};
      //         if ($perfil && !empty($perfil->Correo)) {
      //             Log::info($perfil->Correo);
      //             Mail::to($perfil->Correo)->queue(new NoticiasMail($request->Titulo, $request->Descripcion));
      //         }
      //     }
      // }

      // if (filter_var($request->push, FILTER_VALIDATE_BOOLEAN)) {
      //     EnviarNotificacionNoticia::dispatch($rolId, $noticia->id);
      // }
   }

   public function getAll(): Collection
   {
      return Noticia::where('Vencimiento', '>', now())->orderBy('created_at', 'desc')->get();
   }

   public function update(array $request, Noticia $noticia): void
   {
      $noticia->update([
         "Titulo" => $request['Titulo'],
         "Descripcion" => $request['Descripcion'],
         "Vencimiento" => $request['Vencimiento'],
         "Fecha" => $request['Fecha'],
         "Hora" => $request['Hora'],
         "Tipo" => $request['Tipo'],
         "Correo" => $request['Correo'],
         "Push" => $request['Push'],
         "Destinatario" => (int) $request['Destinatario'],
      ]);
   }

   public function delete(int $id): void
   {
      $noticia = Noticia::findOrFail($id);
      $noticia->delete();
   }
}
