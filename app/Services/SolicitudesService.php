<?php

namespace App\Services;

use App\Models\Solicitudes;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SolicitudesService
{
   public function create(array $request): void
   {
      Solicitudes::create([
         'Descripcion' => $request['Descripcion'],
         'Tipo'        => $request['Tipo'],
         'user_id'     => $request['user_id'],
         'Estado'      => 1
      ]);
   }

   public function getAll(array $filters): LengthAwarePaginator
   {
      $limit = $filters['limit'] ?? 30;
      $search = isset($filters['search']) ? trim($filters['search']) : null;
      $state = $filters['state'] ?? null;

      $searchTerms = $search ? preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) : [];

      $solicitudes = Solicitudes::with(['user.asociado', 'user.adherente'])
         ->when(!empty($searchTerms), function ($query) use ($searchTerms) {
            $query->where(function ($query) use ($searchTerms) {
               $query->whereHas('user.asociado', function ($q) use ($searchTerms) {
                  foreach ($searchTerms as $term) {
                     $q->where(function ($subQuery) use ($term) {
                        $subQuery
                           ->whereRaw("CONCAT(Nombre, ' ', Apellidos) LIKE ?", ["%{$term}%"])
                           ->orWhere('Nombre', 'like', "%{$term}%")
                           ->orWhere('Apellidos', 'like', "%{$term}%");
                     });
                  }
               })
                  ->orWhereHas('user.adherente', function ($q) use ($searchTerms) {
                     foreach ($searchTerms as $term) {
                        $q->where(function ($subQuery) use ($term) {
                           $subQuery
                              ->whereRaw("CONCAT(Nombre, ' ', Apellidos) LIKE ?", ["%{$term}%"])
                              ->orWhere('Nombre', 'like', "%{$term}%")
                              ->orWhere('Apellidos', 'like', "%{$term}%");
                        });
                     }
                  });
            });
         })
         ->when(
            array_key_exists('state', $filters) && $state !== null && $state !== '',
            function ($query) use ($state) {
               $query->where('Estado', (int) $state);
            }
         )
         ->orderBy('Estado', 'desc')
         ->paginate($limit);

      $solicitudes->getCollection()->transform(function ($solicitud) {
         $user = $solicitud->user;
         $usuario = null;

         if ($user) {
            if ((int) $user->Rol === 2 && $user->asociado) {
               $usuario = $user->asociado->withoutRelations();
            }

            if ((int) $user->Rol === 3 && $user->adherente) {
               $usuario = $user->adherente->withoutRelations();
            }

            if ($usuario) {
               $usuario->rol = (int) $user->Rol;
            }
         }

         $solicitud->usuario = $usuario;
         unset($solicitud->user);
         return $solicitud;
      });
      return $solicitudes;
   }

   public function get(int $id): ?Solicitudes
   {
      return Solicitudes::findOrFail($id);
   }

   public function getByUser(int $id, array $filters): LengthAwarePaginator
   {
      $limit = min(max((int) ($filters['limit'] ?? 30), 1), 100);
      $search = isset($filters['search']) ? trim($filters['search']) : null;
      $state = $filters['state'] ?? null;

      $searchTerms = $search ? preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) : [];

      return Solicitudes::where('user_id', $id)
         ->when(!empty($searchTerms), function ($query) use ($searchTerms) {
            $query->where(function ($query) use ($searchTerms) {
               foreach ($searchTerms as $term) {
                  $query->where(function ($query) use ($term) {
                     $query
                        ->where('Descripcion', 'LIKE', "%{$term}%")
                        ->orWhere('Tipo', 'LIKE', "%{$term}%");
                  });
               }
            });
         })
         ->when(
            array_key_exists('state', $filters) && $state !== null && $state !== '',
            function ($query) use ($state) {
               $query->where('Estado', (int) $state);
            }
         )
         ->orderBy('Estado', 'desc')
         ->orderBy('created_at', 'desc')
         ->paginate($limit);
   }

   public function contPendientes(): int
   {
      return Solicitudes::where('Estado', 1)->get()->count();
   }

   public function contByUser(int $id): int
   {
      return Solicitudes::where('user_id', $id)->get()->count();
   }

   public function reply(array $request, int $id): void
   {
      $solicitud = Solicitudes::findOrFail($id);
      $solicitud->update([
         'Respuesta' => $request['Respuesta'],
         'Estado'    => 0
      ]);
   }
}
