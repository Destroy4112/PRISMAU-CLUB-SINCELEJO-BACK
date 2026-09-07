<?php

namespace App\Services;

use App\Models\Reservas;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ReservasService
{

   public function __construct(
      protected DisponibilidadEspacioService $disponibilidadEspacioService
   ) {}

   public function create(array $data): array
   {
      $espacioId = $data['espacio_id'];
      $fecha = $data['Fecha'];
      $horaInicio = $data['Inicio'];
      $horaFin = $data['Fin'];

      $diaSemana = Carbon::parse($fecha)->format('l');

      $diasSemana = [
         'Monday' => 'Lunes',
         'Tuesday' => 'Martes',
         'Wednesday' => 'Miércoles',
         'Thursday' => 'Jueves',
         'Friday' => 'Viernes',
         'Saturday' => 'Sábado',
         'Sunday' => 'Domingo',
      ];

      $diaSemanaEsp = $diasSemana[$diaSemana] ?? null;

      $disponible = $this->disponibilidadEspacioService->consultarDisponibilidad(
         $espacioId,
         $diaSemanaEsp,
         $horaInicio,
         $horaFin
      );

      if (!$disponible) {
         return [
            'status' => false,
            'message' => 'El horario seleccionado no está disponible en este espacio.',
         ];
      }

      $conflicto = $this->verificarDisponibilidad($espacioId, $fecha, $horaInicio, $horaFin);

      if ($conflicto) {
         return [
            'status' => false,
            'message' => 'El horario seleccionado ya está ocupado en este espacio.',
         ];
      }

      Reservas::create($data);

      return [
         'status' => true,
         'message' => 'Reserva creada con éxito',
      ];
   }

   public function getAll(array $filters): LengthAwarePaginator
   {
      $limit = $filters['limit'] ?? 30;
      $search = isset($filters['search']) ? trim($filters['search']) : null;
      $searchTerms = $search ? preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) : [];

      $reservas = Reservas::with(['user.asociado', 'user.adherente', 'espacio'])
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
               })->orWhereHas('user.adherente', function ($q) use ($searchTerms) {
                  foreach ($searchTerms as $term) {
                     $q->where(function ($subQuery) use ($term) {
                        $subQuery
                           ->whereRaw("CONCAT(Nombre, ' ', Apellidos) LIKE ?", ["%{$term}%"])
                           ->orWhere('Nombre', 'like', "%{$term}%")
                           ->orWhere('Apellidos', 'like', "%{$term}%");
                     });
                  }
               })->orWhereHas('espacio', function ($q) use ($searchTerms) {
                  foreach ($searchTerms as $term) {
                     $q->where('Descripcion', 'like', "%{$term}%");
                  }
               });
            });
         })
         ->orderBy('Fecha', 'desc')
         ->paginate($limit);

      $reservas->getCollection()->transform(function ($reserva) {
         $user = $reserva->user;
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

         $reserva->usuario = $usuario;
         unset($reserva->user);
         return $reserva;
      });
      return $reservas;
   }

   public function count(): int
   {
      return Reservas::count();
   }

   public function verificarDisponibilidad(int $espacioId, string $fecha, string $horaInicio, string $horaFin): bool
   {
      return Reservas::where('espacio_id', $espacioId)
         ->where('Fecha', $fecha)
         ->where(function ($query) use ($horaInicio, $horaFin) {
            $query->whereBetween('Inicio', [$horaInicio, $horaFin])
               ->orWhereBetween('Fin', [$horaInicio, $horaFin])
               ->orWhere(function ($sub) use ($horaInicio, $horaFin) {
                  $sub->where('Inicio', '<', $horaInicio)
                     ->where('Fin', '>', $horaFin);
               });
         })
         ->exists();
   }

   public function getByUser(int $id): Collection
   {
      $fechaActual = Carbon::today()->toDateString();
      return Reservas::with('espacio')->where('user_id', $id)->where('Fecha', '>=', $fechaActual)
         ->orderBy('Fecha', 'desc')->get();
   }

   public function countByUser(int $id): int
   {
      $fechaActual = Carbon::today()->toDateString();
      return Reservas::where('user_id', $id)->where('Fecha', '>=', $fechaActual)->count();
   }

   public function cancel(int $id): void
   {
      $reserva = Reservas::findOrFail($id);
      $reserva->delete();
   }
}
