<?php

namespace App\Services;

use App\Mail\EstadosMail;
use App\Mail\PagoEmail;
use App\Models\Adherente;
use App\Models\CuotasBaile;
use App\Models\Mensualidades;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserService
{

   public function createUser(array $data): User
   {
      $password = $data['password'] ?? $data['Documento'];
      return User::create([
         'Documento' => $data['Documento'],
         'password' => Hash::make($password),
         'Rol' => $data['Rol'],
      ]);
   }

   public function getSociosForPayments(array $filters = []): LengthAwarePaginator
   {
      $limit = min(max((int) ($filters['limit'] ?? 30), 1), 100);
      $search = trim((string) ($filters['search'] ?? ''));
      $searchTerms = $search !== '' ? preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) : [];
      $state = isset($filters['state']) && $filters['state'] !== '' ? (int) $filters['state'] : null;

      $userRelations = ['asociado', 'adherente'];

      $socios = User::query()->with([
         'asociado',
         'adherente',
         'mensualidades' => fn($query) => $query->orderBy('created_at'),
         'cuotas' => fn($query) => $query->orderBy('created_at'),
      ])->whereIn('Rol', [2, 3])
         ->when(!empty($searchTerms), function (Builder $query) use ($searchTerms, $userRelations) {
            foreach ($searchTerms as $term) {
               $query->where(function (Builder $query) use ($term, $userRelations) {
                  foreach ($userRelations as $relation) {
                     $query->orWhereHas($relation, function (Builder $query) use ($term) {
                        $query->where('Nombre', 'like', "%{$term}%")
                           ->orWhere('Apellidos', 'like', "%{$term}%")
                           ->orWhere('Documento', 'like', "%{$term}%");
                     });
                  }
               });
            }
         })
         ->when($state !== null, function (Builder $query) use ($state, $userRelations) {
            $query->where(function (Builder $query) use ($state, $userRelations) {
               foreach ($userRelations as $relation) {
                  $query->orWhereHas($relation, function (Builder $query) use ($state) {
                     $query->where('Estado', $state);
                  });
               }
            });
         })
         ->orderBy('created_at', 'asc')
         ->paginate($limit);

      $socios->getCollection()->transform(function (User $user): array {
         $info = match ((int) $user->Rol) {
            2 => $user->asociado,
            3 => $user->adherente,
            default => null,
         };

         $ultimaMensualidad = $user->mensualidades->first();
         $ultimaCuotaBaile = $user->cuotas->first();

         return [
            'id' => $user->id,
            'imagen' => $info?->imagen,
            'nombre' => $info?->Nombre,
            'apellidos' => $info?->Apellidos,
            'tipoDocumento' => $info?->TipoDocumento,
            'documento' => $info?->Documento,
            'sexo' => $info?->Sexo,
            'codigo' => $info?->Codigo,
            'telefono' => $info?->Telefono,
            'direccion' => $info?->Direccion,
            'estado' => $info?->Estado,
            'rol' => (int) $user->Rol,
            'mensualidad' => $ultimaMensualidad?->valor,
            'cuota_baile' => $ultimaCuotaBaile?->valor,
         ];
      });

      return $socios;
   }

   public function getSaldosSocios(array $filters): LengthAwarePaginator
   {
      $limit = min(max((int) ($filters['limit'] ?? 30), 1), 100);
      $search = trim((string) ($filters['search'] ?? ''));
      $searchTerms = $search !== '' ? preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) : [];

      $socios = User::query()
         ->with([
            'asociado:id,user_id,Nombre,Apellidos,TipoDocumento,Documento,Estado',
            'adherente:id,user_id,Nombre,Apellidos,TipoDocumento,Documento,Estado',
         ])
         ->withCount([
            'mensualidades as meses_mensualidad_pendientes' => function (Builder $query) {
               $query->where('estado', false);
            },

            'cuotas as meses_cuota_baile_pendientes' => function (Builder $query) {
               $query->where('estado', false);
            },
         ])
         ->whereIn('Rol', [2, 3])

         ->when(!empty($searchTerms), function (Builder $query) use ($searchTerms) {
            foreach ($searchTerms as $term) {
               $query->where(function (Builder $query) use ($term) {
                  $query
                     ->whereHas('asociado', function (Builder $query) use ($term) {
                        $query
                           ->where('Nombre', 'like', "%{$term}%")
                           ->orWhere('Apellidos', 'like', "%{$term}%")
                           ->orWhere('Documento', 'like', "%{$term}%");
                     })
                     ->orWhereHas('adherente', function (Builder $query) use ($term) {
                        $query
                           ->where('Nombre', 'like', "%{$term}%")
                           ->orWhere('Apellidos', 'like', "%{$term}%")
                           ->orWhere('Documento', 'like', "%{$term}%");
                     });
               });
            }
         })
         ->orderByDesc('id')
         ->paginate($limit);

      $socios->getCollection()->transform(function (User $user): array {
         $info = match ((int) $user->Rol) {
            2 => $user->asociado,
            3 => $user->adherente,
            default => null,
         };

         return [
            'id'            => $user->id,
            'nombre'        => trim($info?->Nombre ?? '') ?: 'No disponible',
            'apellidos'     => trim($info?->Apellidos ?? '') ?: 'No disponible',
            'documento'     => $info?->Documento,
            'tipoDocumento' => $info?->TipoDocumento,
            'estado'        => $info?->Estado,
            'rol'           => (int) $user->Rol,
            'mensualidades' => (int) $user->meses_mensualidad_pendientes,
            'cuotas'        => (int) $user->meses_cuota_baile_pendientes,
         ];
      });

      return $socios;
   }

   public function getUser(int $id): ?User
   {
      return User::findOrFail($id);
   }

   public function getUserByDocumento(string $documento): User
   {
      return User::where('Documento', $documento)->firstOrFail();
   }

   public function getContabilidadGeneral(): array
   {
      $ingresosMensualidades = (float) DB::table('pagos')->sum('monto');
      $ingresosCuotas = (float) DB::table('pagos_cuotas_bailes')->sum('monto');

      $pendientesMensualidades = $this->obtenerPendientes(
         tablaDeudas: 'mensualidades',
         tablaPagos: 'pagos',
         foreignKey: 'mensualidad_id'
      );

      $pendientesCuotas = $this->obtenerPendientes(
         tablaDeudas: 'cuotas_bailes',
         tablaPagos: 'pagos_cuotas_bailes',
         foreignKey: 'cuotas_baile_id'
      );

      return [
         'ingresos' => [
            'mensualidades' => $ingresosMensualidades,
            'cuotas_baile' => $ingresosCuotas,
            'total' => $ingresosMensualidades + $ingresosCuotas,
         ],
         'pendientes' => [
            'mensualidades' => $pendientesMensualidades,
            'cuotas_baile' => $pendientesCuotas,
         ],
      ];
   }

   private function obtenerPendientes(string $tablaDeudas, string $tablaPagos, string $foreignKey): array
   {
      $pagosAgrupados = DB::table($tablaPagos)->select($foreignKey)->selectRaw('SUM(monto) AS pagado')->groupBy($foreignKey);
      $resultado = DB::table("{$tablaDeudas} as deuda")
         ->leftJoinSub($pagosAgrupados, 'pago', function ($join) use ($foreignKey) {
            $join->on("pago.{$foreignKey}", '=', 'deuda.id');
         })
         ->selectRaw('
            SUM(
                CASE
                    WHEN deuda.valor > COALESCE(pago.pagado, 0)
                    THEN 1
                    ELSE 0
                END
            ) AS registros,
            SUM(
                CASE
                    WHEN deuda.valor > COALESCE(pago.pagado, 0)
                    THEN deuda.valor - COALESCE(pago.pagado, 0)
                    ELSE 0
                END
            ) AS monto
        ')->first();

      return [
         'registros' => (int) ($resultado->registros ?? 0),
         'monto' => (float) ($resultado->monto ?? 0),
      ];
   }

   public function getByDocumento(string $documento): array
   {
      $user = User::with([
         'asociado.familiares',
         'adherente.familiares',
         'empleado',
         'familiar.adherente.familiares',
         'familiar.asociado.familiares',
      ])->where('Documento', $documento)->first();

      if (!$user) {
         return [
            "status" => false,
            "message" => "Usuario no encontrado",
         ];
      }

      $usuario = null;
      $familiares = [];
      $relacionado = null;
      $relacionadoData = null;
      $tipo = null;

      switch ((int) $user->Rol) {
         case 2:
            $usuario = $user->asociado?->withoutRelations()->toArray();
            $familiares = $user->asociado?->familiares
               ->map(fn($f) => $f->withoutRelations()->toArray())
               ->all() ?? [];
            $tipo = "ASOCIADO";
            break;

         case 3:
            $usuario = $user->adherente?->withoutRelations()->toArray();
            $familiares = $user->adherente?->familiares
               ->map(fn($f) => $f->withoutRelations()->toArray())
               ->all() ?? [];
            $tipo = "ADHERENTE";
            break;

         case 4:
         case 6:
         case 7:
         case 8:
         case 9:
         case 10:
            $usuario = $user->empleado?->withoutRelations()->toArray();
            $tipo = "EMPLEADO";
            break;

         case 5:
            $familiar = $user->familiar;

            if ($familiar) {
               $usuario = $familiar->withoutRelations()->toArray();
               $relacionado = $familiar->adherente ?? $familiar->asociado;

               if ($relacionado) {
                  $relacionadoData = $relacionado->withoutRelations()->toArray();
                  $relacionadoData['user_id'] = $relacionado instanceof Adherente ? 3 : 2;

                  $familiares = $relacionado->familiares
                     ->filter(fn($f) => $f->id !== $familiar->id)
                     ->map(fn($f) => $f->withoutRelations()->toArray())
                     ->values()
                     ->all();

                  $familiares[] = array_merge(
                     $relacionado->withoutRelations()->toArray(),
                     ['Parentesco' => $familiar->adherente ? 'adherente' : 'asociado']
                  );
               }
            }

            $tipo = "FAMILIAR";
            break;
      }

      return [
         "status" => true,
         "data" => [
            "tipo" => $tipo,
            "user" => $usuario,
            "relacionado" => $relacionadoData,
            "familiares" => $familiares,
         ]
      ];
   }

   public function updateUser(int $id, array $data): User
   {
      $user = User::find($id);
      $rol = $data['Rol'] ?? $user->Rol;
      $user->update([
         'Documento' => $data['Documento'],
         'password' => Hash::make($data['Documento']),
         'Rol' => $rol
      ]);
      return $user;
   }

   public function changePassword(array $request, int $id): void
   {
      $usuario = User::findOrFail($id);
      $usuario->password = Hash::make($request['password']);
      $usuario->save();
   }

   public function resetPassword(int $id): void
   {
      $user = User::findOrFail($id);
      $user->update(['password' => Hash::make($user->Documento)]);
   }

   public function confirmarPagoMensualidad(int $userId, int $mensualidadId, string $estado): void
   {
      $user = User::with(['asociado', 'adherente'])->findOrFail($userId);
      $mensualidad = Mensualidades::findOrFail($mensualidadId);
      $socio = $user->asociado ?? $user->adherente;

      if (!$socio || empty($socio->Correo)) {
         return;
      }

      $periodo = Carbon::parse($mensualidad->fecha)->locale('es')->translatedFormat('F \d\e Y');

      Mail::to($socio->Correo)->send(new PagoEmail($estado, $periodo, (float) $mensualidad->valor));
   }

   public function confirmarPagoCuotaBaile(int $userId, int $cuotaId, string $estado): void
   {
      $user = User::with(['asociado', 'adherente'])->findOrFail($userId);
      $cuota = CuotasBaile::findOrFail($cuotaId);
      $socio = $user->asociado ?? $user->adherente;

      if (!$socio || empty($socio->Correo)) {
         return;
      }

      Mail::to($socio->Correo)->send(new pagoEmail($estado, $cuota->descripcion, $cuota->valor));
   }

   public function deleteUser(int $id): void
   {
      $user = User::find($id);
      $user->delete();
   }

   public function eliminarCuenta(String $id)
   {
      $user = User::find($id);
      if ($user->Rol == 2) {
         $email = $user->asociado->Correo;
      } else if ($user->Rol == 3) {
         $email = $user->adherente->Correo;
      }
      $fecha = now()->format('d/m/Y');
      $content = <<<HTML
                        <h1>Club Sincelejo</h1>
                        <p><strong>Fecha:</strong> {$fecha}</p>
                        <h3>Cordial saludo,</h3>
                        <p>Queremos informarle que hemos recibido su solicitud para eliminación de cuenta en los proximos dias estaremos comunicandonos nuevamente con usted.</p>
                        <p>Gracias.</p>
                        HTML;
      Mail::to($email)->send(new EstadosMail($content, null));
      return [
         "status" => true,
         "message" => "hecho"
      ];
   }
}
