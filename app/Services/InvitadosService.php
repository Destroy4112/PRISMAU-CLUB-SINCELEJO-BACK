<?php

namespace App\Services;

use App\Models\Invitado;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InvitadosService
{

   public function create(array $data): array
   {
      return DB::transaction(function () use ($data) {
         User::query()->whereKey($data['user_id'])->lockForUpdate()->firstOrFail();

         if ($this->cantidadInvitacionesDelMes($data['Documento']) >= 2) {
            return [
               'status' => false,
               'message' => 'Esta persona ya alcanzó el límite de dos invitaciones durante el mes actual.',
            ];
         }

         if (!$this->puedeSerInvitado($data['Documento'])) {
            return [
               'status' => false,
               'message' => 'La persona no puede ser invitada debido a su estado actual.',
            ];
         }

         $socio = $this->obtenerUsuarioInfo($data['user_id']);

         $invitado = Invitado::create([
            'user_id' => $data['user_id'],
            'Nombre' => $data['Nombre'],
            'Apellidos' => $data['Apellidos'],
            'TipoDocumento' => $data['TipoDocumento'],
            'Documento' => $data['Documento'],
            'Telefono' => $data['Telefono'],
            'Status' => false,
         ]);

         $invitado->setAttribute('socio', $socio);

         return [
            'status' => true,
            'message' => 'Invitación generada con éxito.',
            'data' => $invitado,
         ];
      });
   }

   public function saveImagen(UploadedFile $imagen, int $id): void
   {
      $invitacion = Invitado::findOrFail($id);

      $nameImage = Str::slug($invitacion->id) . '_' . Str::uuid() . '.' . $imagen->getClientOriginalExtension();
      $path = $imagen->storeAs('invitaciones', $nameImage, 'public');

      $invitacion->imagen = Storage::url($path);
      $invitacion->save();
   }

   public function getAll(array $filters): LengthAwarePaginator
   {
      $limit = min(max((int) ($filters['limit'] ?? 30), 1), 100);
      $search = trim((string) ($filters['search'] ?? ''));

      $searchTerms = $search !== '' ? preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) : [];

      $invitados = Invitado::query()
         ->with(['user.asociado', 'user.adherente', 'user.familiar',])
         ->when(!empty($searchTerms), function (Builder $query) use ($searchTerms) {
            $this->applySearch($query, $searchTerms);
         })
         ->orderByDesc('created_at')
         ->paginate($limit);

      $this->transformInvitados($invitados);

      return $invitados;
   }

   public function getEntradas(array $filters): LengthAwarePaginator
   {
      $limit = min(max((int) ($filters['limit'] ?? 30), 1), 100);
      $search = trim((string) ($filters['search'] ?? ''));

      $searchTerms = $search !== '' ? preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) : [];

      $invitados = Invitado::query()
         ->with(['user.asociado', 'user.adherente', 'user.familiar',])
         ->where('Status', true)
         ->when(!empty($searchTerms), function (Builder $query) use ($searchTerms) {
            $this->applySearch($query, $searchTerms);
         })
         ->orderByDesc('created_at')
         ->paginate($limit);

      $this->transformInvitados($invitados);

      return $invitados;
   }

   public function updateEntrada(int $id): void
   {
      $invitado = Invitado::find($id);
      $invitado->update(['Status' => true,]);
   }

   //-------------------- HELPERS --------------------------------------------------

   private function cantidadInvitacionesDelMes(string $documento): int
   {
      $inicioMes = now()->startOfMonth();
      $inicioSiguienteMes = now()->addMonthNoOverflow()->startOfMonth();

      return Invitado::query()
         ->where('Documento', $documento)
         ->where('created_at', '>=', $inicioMes)
         ->where('created_at', '<', $inicioSiguienteMes)
         ->count();
   }

   private function obtenerUsuarioInfo(int $id): ?object
   {
      $user = User::with(['asociado', 'adherente', 'familiar',])->findOrFail($id);

      $relacion = match ((int) $user->Rol) {
         2 => 'asociado',
         3 => 'adherente',
         5 => 'familiar',
         default => null,
      };

      if ($relacion === null) {
         return null;
      }

      return $user->{$relacion}?->withoutRelations();
   }

   private function puedeSerInvitado(string $documento): bool
   {
      $user = User::with(['asociado', 'adherente',])->where('Documento', $documento)->first();
      if (!$user) {
         return true;
      }

      $rol = (int) $user->Rol;

      if (!in_array($rol, [2, 3], true)) {
         return true;
      }

      $socio = match ($rol) {
         2 => $user->asociado,
         3 => $user->adherente,
      };

      if (!$socio) {
         return false;
      }

      return !in_array((int) $socio->Estado, [0, 3, 4], true);
   }

   private function applySearch(Builder $query, array $searchTerms): void
   {
      foreach ($searchTerms as $term) {
         $query->where(function (Builder $query) use ($term) {
            $query
               ->where('Nombre', 'like', "%{$term}%")
               ->orWhere('Apellidos', 'like', "%{$term}%")
               ->orWhere('Documento', 'like', "%{$term}%")
               ->orWhere('Telefono', 'like', "%{$term}%")
               ->orWhereRaw("CONCAT(Nombre, ' ', Apellidos) LIKE ?", ["%{$term}%"])

               ->orWhereHas('user', function (Builder $query) use ($term) {
                  $query->where(function (Builder $query) use ($term) {
                     $this->searchUserRelation($query, 'asociado', $term);
                     $this->searchUserRelation($query, 'adherente', $term);
                     $this->searchUserRelation($query, 'familiar', $term);
                  });
               });
         });
      }
   }

   private function searchUserRelation(Builder $query, string $relation, string $term): void
   {
      $query->orWhereHas(
         $relation,
         function (Builder $query) use ($term) {
            $query
               ->where('Nombre', 'like', "%{$term}%")
               ->orWhere('Apellidos', 'like', "%{$term}%")
               ->orWhere('Documento', 'like', "%{$term}%")
               ->orWhereRaw("CONCAT(Nombre, ' ', Apellidos) LIKE ?", ["%{$term}%"]);
         }
      );
   }

   private function transformInvitados(LengthAwarePaginator $invitados): LengthAwarePaginator
   {
      $collection = $invitados->getCollection()->map(
         function (Invitado $invitado): Invitado {
            $user = $invitado->user;
            $usuario = null;

            if ($user) {
               $usuario = match ((int) $user->Rol) {
                  2 => $user->asociado,
                  3 => $user->adherente,
                  5 => $user->familiar,
                  default => null,
               };

               if ($usuario) {
                  $usuario = $usuario->withoutRelations();
                  $usuario->setAttribute('rol', (int) $user->Rol);
               }
            }

            $invitado->setAttribute('usuario', $usuario);
            $invitado->unsetRelation('user');

            return $invitado;
         }
      );

      $invitados->setCollection($collection);

      return $invitados;
   }
}
