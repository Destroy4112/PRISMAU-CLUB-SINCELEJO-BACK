<?php

namespace App\Services;

use App\Mail\EstadosMail;
use App\Models\Asociado;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class AsociadoService
{

   public function __construct(
      protected UserService $usuarioService,
      protected EstadoService $estadoService,
      protected SocioService $socioService
   ) {}


   public function create(array $data): Asociado
   {
      return DB::transaction(function () use ($data) {

         $user = $this->usuarioService->createUser(['Documento' => $data['Documento'], 'Rol' => 2]);

         return Asociado::create([
            'user_id' => $user->id,
            'Nombre' => $data['Nombre'],
            'Apellidos' => $data['Apellidos'],
            'TipoDocumento' => $data['TipoDocumento'],
            'Documento' => $data['Documento'],
            'Correo' => $data['Correo'],
            'Telefono' => $data['Telefono'],
            'FechaNacimiento' => $data['FechaNacimiento'],
            'LugarNacimiento' => $data['LugarNacimiento'],
            'Sexo' => $data['Sexo'],
            'Codigo' => $data['Codigo'],
            'DireccionResidencia' => $data['DireccionResidencia'],
            'CiudadResidencia' => $data['CiudadResidencia'],
            'TiempoResidencia' => $data['TiempoResidencia'],
            'EstadoCivil' => $data['EstadoCivil'],
            'Profesion' => $data['Profesion'],
            'Trabajo' => $data['Trabajo'],
            'Cargo' => $data['Cargo'],
            'TiempoServicio' => $data['TiempoServicio'],
            'TelOficina' => $data['TelOficina'],
            'DireccionOficina' => $data['DireccionOficina'],
            'CiudadOficina' => $data['CiudadOficina'],
            'Estado' => $data['Estado']
         ]);
      });
   }

   public function changeImagen(UploadedFile $imagen, int $id): void
   {
      $asociado = Asociado::findOrFail($id);

      $oldImage = $asociado->imagen;

      $nameImage = Str::slug($asociado->Documento) . '_' . Str::uuid() . '.' . $imagen->getClientOriginalExtension();
      $path = $imagen->storeAs('personal', $nameImage, 'public');

      $asociado->imagen = Storage::url($path);
      $asociado->save();

      if ($oldImage) {
         $oldPath = ltrim(str_replace('/storage/', '', $oldImage), '/');

         if (Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
         }
      }
   }

   public function getAll(array $filters): LengthAwarePaginator
   {
      $limit = $filters['limit'] ?? 30;
      $search = isset($filters['search']) ? trim($filters['search']) : null;
      $searchTerms = $search ? preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) : [];
      $state = $filters['state'] ?? null;

      $query = Asociado::withCount('familiares')
         ->when(!empty($searchTerms), function ($query) use ($searchTerms) {
            foreach ($searchTerms as $term) {
               $query->where(function ($q) use ($term) {
                  $q->where('Nombre', 'LIKE', "%{$term}%")
                     ->orWhere('Apellidos', 'LIKE', "%{$term}%")
                     ->orWhere('Documento', 'LIKE', "%{$term}%");
               });
            }
         })
         ->when(
            array_key_exists('state', $filters) && $state !== null && $state !== '',
            function ($query) use ($state) {
               $query->where('Estado', (int) $state);
            }
         )
         ->orderBy('Nombre', 'asc')
         ->orderBy('Apellidos', 'asc')
         ->paginate($limit);

      return $query;
   }

   public function getAsociados(): Collection
   {
      return Asociado::all();
   }

   public function asociadoWithFamiliars(int $id): Collection
   {
      return Asociado::with(['familiares' => function ($query) {
         $query->select('id', 'asociado_id', 'Nombre', 'Apellidos', 'parentesco');
      }])->select('id', 'imagen', 'Nombre', 'Apellidos', 'TipoDocumento', 'Documento', 'Estado')->find($id);
   }

   public function update(array $data, Asociado $asociado): void
   {
      DB::transaction(function () use ($data, $asociado) {
         if ($data['Documento'] != $asociado->user->Documento) {
            $this->usuarioService->updateUser($asociado->user->id, [
               'Documento' => $data['Documento'],
            ]);
         }
         if ($asociado->Codigo != $data['Codigo']) {
            $asociado->familiares()->update(['Codigo' => $data['Codigo']]);
         }
         $asociado->update([
            "Nombre" => $data['Nombre'],
            "Apellidos" => $data['Apellidos'],
            "TipoDocumento" => $data['TipoDocumento'],
            "Documento" => $data['Documento'],
            "Correo" => $data['Correo'],
            "Telefono" => $data['Telefono'],
            "FechaNacimiento" => $data['FechaNacimiento'],
            "LugarNacimiento" => $data['LugarNacimiento'],
            "Sexo" => $data['Sexo'],
            'Codigo' => $data['Codigo'],
            "DireccionResidencia" => $data['DireccionResidencia'],
            "CiudadResidencia" => $data['CiudadResidencia'],
            "TiempoResidencia" => $data['TiempoResidencia'],
            "EstadoCivil" => $data['EstadoCivil'],
            "Profesion" => $data['Profesion'],
            "Trabajo" => $data['Trabajo'],
            "Cargo" => $data['Cargo'],
            "TiempoServicio" => $data['TiempoServicio'],
            "TelOficina" => $data['TelOficina'],
            "DireccionOficina" => $data['DireccionOficina'],
            "CiudadOficina" => $data['CiudadOficina'],
         ]);
      });
   }

   public function changeStatus(array $data, int $id): void
   {
      DB::transaction(function () use ($data, $id) {
         $asociado = Asociado::findOrFail($id);

         $estado = (int) $data['Estado'];
         $motivo = $data['Motivo'];

         $estadoString = match ($estado) {
            0 => 'Inactivo',
            1 => 'Activo',
            2 => 'Retirado',
            3 => 'Mora',
            4 => 'Retirado en mora',
            default => 'Estado no reconocido',
         };

         $asociado->familiares()->update(['Estado' => $estado]);

         $this->socioService->cambiarEstadoGrupo($id, $estado);

         $this->estadoService->create([
            'user_id' => $asociado->user_id,
            'Estado' => $estadoString,
            'Motivo' => $motivo,
         ]);

         $asociado->update(['Estado' => $estado,]);

         DB::afterCommit(function () use ($asociado, $estadoString, $motivo) {
            Mail::to($asociado->Correo)->send(new EstadosMail($estadoString, $motivo));
         });
      });
   }

   public function delete(int $id): void
   {
      DB::transaction(function () use ($id) {

         $asociado = Asociado::with('familiares')->findOrFail($id);

         if ($asociado->imagen) {
            $oldPath = ltrim(str_replace('/storage/', '', $asociado->imagen), '/');

            if (Storage::disk('public')->exists($oldPath)) {
               Storage::disk('public')->delete($oldPath);
            }
         }

         foreach ($asociado->familiares as $familiar) {
            if ($familiar->imagen) {
               $familiarImagePath = ltrim(str_replace('/storage/', '', $familiar->imagen), '/');

               if (Storage::disk('public')->exists($familiarImagePath)) {
                  Storage::disk('public')->delete($familiarImagePath);
               }
            }

            $this->usuarioService->deleteUser($familiar->user_id);
            $familiar->delete();
         }

         $this->usuarioService->deleteUser($asociado->user_id);
         $asociado->delete();
      });
   }

   public function deleteImagen(int $id): void
   {
      $asociado = Asociado::findOrFail($id);

      if ($asociado->imagen) {
         $oldPath = ltrim(str_replace('/storage/', '', $asociado->imagen), '/');

         if (Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
         }
      }

      $asociado->imagen = null;
      $asociado->save();
   }
}
