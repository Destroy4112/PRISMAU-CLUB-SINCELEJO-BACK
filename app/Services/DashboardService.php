<?php

namespace App\Services;

use App\Models\Adherente;
use App\Models\Admin;
use App\Models\Asociado;
use App\Models\Contratos;
use App\Models\Empleado;
use App\Models\Encuestas;
use App\Models\Espacio;
use App\Models\Familiar;
use App\Models\Invitado;
use App\Models\Menu;
use App\Models\Noticia;
use App\Models\Reservas;
use App\Models\Solicitudes;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardService
{

   public function getStats(User $user): array
   {
      $rol = (int) ($user->Rol ?? 99);

      $cacheKey = "dash:stats:u:{$user->id}:rol:{$rol}";
      return Cache::remember($cacheKey, now()->addSeconds(60), function () use ($user, $rol) {
         return match ($rol) {
            0 => array_merge(
               $this->superAdminStats(),
               $this->adminStats()
            ),
            1 => $this->adminStats(),
            2, 3 => $this->socioStats($user),
            default => [],
         };
      });
   }

   private function superAdminStats(): array
   {
      return [
         'contContrataciones' => Contratos::count(),
         'contAdmins' => Admin::whereHas('user', fn($q) => $q->where('Rol', 1))->count(),
         'contModulos' => Menu::count(),
         'contRoles' => 5,
         'contHobbies' => 12,
      ];
   }

   private function adminStats(): array
   {
      return [
         'contSolicitudes' => Solicitudes::count(),
         'contReservas' => Reservas::count(),
         'contEncuestas' => Encuestas::count(),
         'contFamiliares' => Familiar::count(),
         'contAsociados' => Asociado::count(),
         'contAdherentes' => Adherente::count(),
         'contEmpleados' => Empleado::count(),
         'contEspacios' => Espacio::count(),
         'contNoticias' => Noticia::count(),
         'contInvitados' => Invitado::count(),
      ];
   }

   private function socioStats(User $user): array
   {
      $estadisticasFinancieras = [
         'contMensualidadesPendientes' => $user->mensualidades()->where('estado', false)->count(),
         'contCuotasBailePendientes' => $user->cuotas()->where('estado', false)->count(),
      ];

      if ($user->Rol == 2) {
         return [
            'contFamiliaresSocio' => Familiar::where('asociado_id', $user->asociado->id)->count(),
            ...$estadisticasFinancieras,
            'contHobbies' => 12,
         ];
      }

      return [
         'contFamiliaresSocio' => Familiar::where('user_id', $user->adherente->id)->count(),
         ...$estadisticasFinancieras,
         'contHobbies' => 12,
      ];
   }
}
