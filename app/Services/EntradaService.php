<?php

namespace App\Services;

use App\Models\Entrada;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EntradaService
{

   public function crearEntrada(int $id): void
   {
      Entrada::create(["user_id" => $id]);
   }

   public function getAll(array $filters): LengthAwarePaginator
   {
      $limit = min(max((int) ($filters['limit'] ?? 30), 1), 100);
      $search = trim((string) ($filters['search'] ?? ''));
      $searchTerms = $search !== '' ? preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) : [];

      $userRelations = ['asociado', 'adherente', 'familiar', 'empleado',];

      $entradas = Entrada::query()->with(['user.asociado', 'user.adherente', 'user.familiar', 'user.empleado'])
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
         ->orderByDesc('created_at')
         ->paginate($limit);

      $entradas->getCollection()->transform(function (Entrada $entrada): Entrada {
         $user = $entrada->user;
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

         $entrada->setAttribute('usuario', $usuario);
         $entrada->unsetRelation('user');

         return $entrada;
      });

      return $entradas;
   }
}
