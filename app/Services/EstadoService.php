<?php

namespace App\Services;

use App\Models\Estados;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EstadoService
{

   public function create(array $data): Estados
   {
      return Estados::create([
         'user_id' => $data['user_id'],
         'Estado' => $data['Estado'],
         'Motivo' => $data['Motivo'],
      ]);
   }

   public function getAll(array $filters): LengthAwarePaginator
   {
      $limit = min(max((int) ($filters['limit'] ?? 30), 1), 100);
      $search = trim((string) ($filters['search'] ?? ''));
      $searchTerms = $search !== '' ? preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) : [];
      $state = $filters['state'] ?? null;

      $userRelations = ['asociado', 'adherente'];

      $estados = Estados::query()->with(['user.asociado', 'user.adherente'])
         ->when(!empty($searchTerms), function (Builder $query) use ($searchTerms, $userRelations) {
            foreach ($searchTerms as $term) {
               $query->whereHas('user', function (Builder $query) use ($term, $userRelations) {
                  $query->where(function (Builder $query) use ($term, $userRelations) {
                     foreach ($userRelations as $relation) {
                        $query->orWhereHas(
                           $relation,
                           function (Builder $query) use ($term) {
                              $query
                                 ->where('Nombre', 'like', "%{$term}%")
                                 ->orWhere('Apellidos', 'like', "%{$term}%")
                                 ->orWhere('Documento', 'like', "%{$term}%");
                           }
                        );
                     }
                  });
               });
            }
         })
         ->when(
            array_key_exists('state', $filters) && $state !== null && $state !== '',
            function ($query) use ($state) {
               $query->where('Estado', $state);
            }
         )
         ->orderByDesc('created_at')
         ->paginate($limit);

      $estados->getCollection()->transform(function (Estados $estado): Estados {
         $user = $estado->user;
         $usuario = null;

         if ($user) {
            $usuario = match ((int) $user->Rol) {
               2 => $user->asociado,
               3 => $user->adherente,
               4 => $user->empleado,
               5 => $user->familiar,
               default => null,
            };

            if ($usuario) {
               $usuario = $usuario->withoutRelations();
               $usuario->setAttribute('rol', (int) $user->Rol);
            }
         }

         $estado->setAttribute('usuario', $usuario);
         $estado->unsetRelation('user');

         return $estado;
      });

      return $estados;
   }
}
