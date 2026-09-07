<?php

namespace App\Services;

use App\Models\Familiar;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FamiliarService
{

   public function __construct(protected UserService $userService) {}

   public function create(array $data): void
   {
      DB::transaction(function () use ($data) {
         $user = $this->userService->createUser(['Documento' => $data['Documento'], 'Rol' => 5]);
         Familiar::create([
            'user_id' => $user->id,
            'asociado_id' => $data['asociado_id'] ?? null,
            'adherente_id' => $data['adherente_id'] ?? null,
            'Nombre' => $data['Nombre'] ?? null,
            'Apellidos' => $data['Apellidos'] ?? null,
            'Correo' => $data['Correo'] ?? null,
            'Telefono' => $data['Telefono'] ?? null,
            'FechaNacimiento' => $data['FechaNacimiento'] ?? null,
            'LugarNacimiento' => $data['LugarNacimiento'] ?? null,
            'TipoDocumento' => $data['TipoDocumento'] ?? null,
            'Documento' => $data['Documento'] ?? null,
            'Sexo' => $data['Sexo'] ?? null,
            'Codigo' => $data['Codigo'] ?? null,
            'DireccionResidencia' => $data['DireccionResidencia'] ?? null,
            'CiudadResidencia' => $data['CiudadResidencia'] ?? null,
            'EstadoCivil' => $data['EstadoCivil'] ?? null,
            'Cargo' => $data['Cargo'] ?? null,
            'Parentesco' => $data['Parentesco'] ?? null,
            'Estado' => $data['Estado'] ?? null,
         ]);
      });
   }

   public function changeImagen(UploadedFile $imagen, int $id): void
   {
      $familiar = Familiar::findOrFail($id);
      $oldImage = $familiar->imagen;

      $nameImage = Str::slug($familiar->Documento) . '_' . Str::uuid() . '.' . $imagen->getClientOriginalExtension();
      $path = $imagen->storeAs('familiares', $nameImage, 'public');

      $familiar->imagen = Storage::url($path);
      $familiar->save();

      if ($oldImage) {
         $oldPath = ltrim(str_replace('/storage/', '', $oldImage), '/');

         if (Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
         }
      }
   }

   public function get(int $id, string $rol): Collection
   {
      $campo = match ($rol) {
         'Adherente' => 'adherente_id',
         'Asociado'  => 'asociado_id',
         default     => throw new \InvalidArgumentException("Rol no válido: {$rol}"),
      };

      return Familiar::where($campo, $id)->get();
   }

   public function nucleoDesdeEsposa(int $id): Collection
   {
      $familiar = Familiar::findOrFail($id);

      if ($familiar->asociado_id) {
         $familiar->load('asociado.familiares');
         $socio = $familiar->asociado;
      } elseif ($familiar->adherente_id) {
         $familiar->load('adherente.familiares');
         $socio = $familiar->adherente;
      } else {
         return collect();
      }

      return collect([$socio])->concat($socio->familiares->where('id', '!=', $id))->values();
   }

   public function contSocio(int $id, string $rol): int
   {
      $campo = match ($rol) {
         'Adherente' => 'adherente_id',
         'Asociado'  => 'asociado_id',
         default     => throw new \InvalidArgumentException("Rol no válido: {$rol}"),
      };

      return Familiar::where($campo, $id)->get()->count();
   }

   public function update(array $data, Familiar $familiar): void
   {
      DB::transaction(function () use ($data, $familiar) {
         if ($data['Documento'] != $familiar->user->Documento) {
            $this->userService->updateUser($familiar->user->id, [
               'Documento' => $data['Documento'],
               'Rol' => $data['Rol']
            ]);
         }
         $familiar->update([
            'Nombre' => $data['Nombre'] ?? null,
            'Apellidos' => $data['Apellidos'] ?? null,
            'Correo' => $data['Correo'] ?? null,
            'Telefono' => $data['Telefono'] ?? null,
            'FechaNacimiento' => $data['FechaNacimiento'] ?? null,
            'LugarNacimiento' => $data['LugarNacimiento'] ?? null,
            'TipoDocumento' => $data['TipoDocumento'] ?? null,
            'Documento' => $data['Documento'] ?? null,
            'Sexo' => $data['Sexo'] ?? null,
            'DireccionResidencia' => $data['DireccionResidencia'] ?? null,
            'CiudadResidencia' => $data['CiudadResidencia'] ?? null,
            'EstadoCivil' => $data['EstadoCivil'] ?? null,
            'Parentesco' => $data['Parentesco'] ?? null,
         ]);
      });
   }

   public function delete(int $id): void
   {
      DB::transaction(function () use ($id) {

         $familiar = Familiar::findOrFail($id);

         if ($familiar->imagen) {
            $oldPath = ltrim(str_replace('/storage/', '', $familiar->imagen), '/');

            if (Storage::disk('public')->exists($oldPath)) {
               Storage::disk('public')->delete($oldPath);
            }
         }

         $this->userService->deleteUser($familiar->user_id);
         $familiar->delete();
      });
   }

   public function deleteImagen(int $id): void
   {
      $familiar = Familiar::findOrFail($id);

      if ($familiar->imagen) {
         $oldPath = ltrim(str_replace('/storage/', '', $familiar->imagen), '/');

         if (Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
         }
      }

      $familiar->imagen = null;
      $familiar->save();
   }
}
