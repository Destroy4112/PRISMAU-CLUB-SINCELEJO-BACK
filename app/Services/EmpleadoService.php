<?php

namespace App\Services;

use App\Models\Empleado;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmpleadoService
{

   public function __construct(protected UserService $usuarioService) {}

   public function create(array $data): void
   {
      DB::transaction(function () use ($data) {
         $user = $this->usuarioService->createUser(['Documento' => $data['Documento'], 'Rol' => $data['Rol']]);

         Empleado::create([
            'user_id' => $user->id,
            'Nombre' => $data['Nombre'],
            'Apellidos' => $data['Apellidos'],
            'Correo' => $data['Correo'],
            'Telefono' => $data['Telefono'],
            'FechaNacimiento' => $data['FechaNacimiento'],
            'LugarNacimiento' => $data['LugarNacimiento'],
            'TipoDocumento' => $data['TipoDocumento'],
            'Documento' => $data['Documento'],
            'Sexo' => $data['Sexo'],
            'DireccionResidencia' => $data['DireccionResidencia'],
            'CiudadResidencia' => $data['CiudadResidencia'],
            'EstadoCivil' => $data['EstadoCivil'],
            'Cargo' => $data['Cargo'],
            'Estado' => $data['Estado'],
         ]);
      });
   }

   public function changeImagen(UploadedFile $imagen, int $id): void
   {
      $empleado = Empleado::findOrFail($id);
      $oldImage = $empleado->imagen;

      $nameImage = Str::slug($empleado->Documento) . '_' . Str::uuid() . '.' . $imagen->getClientOriginalExtension();
      $path = $imagen->storeAs('empleados', $nameImage, 'public');

      $empleado->imagen = Storage::url($path);
      $empleado->save();

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

      $query = Empleado::query()
         ->when(!empty($searchTerms), function ($query) use ($searchTerms) {
            foreach ($searchTerms as $term) {
               $query->where(function ($q) use ($term) {
                  $q->where('Nombre', 'LIKE', "%{$term}%")
                     ->orWhere('Apellidos', 'LIKE', "%{$term}%")
                     ->orWhere('Documento', 'LIKE', "%{$term}%")
                     ->orWhere('Cargo', 'LIKE', "%{$term}%");
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

   public function update(array $data, Empleado $empleado): void
   {
      DB::transaction(function () use ($data, $empleado) {
         if ($data['Documento'] != $empleado->user->Documento || $data['Rol'] != $empleado->user->Rol) {
            $this->usuarioService->updateUser($empleado->user->id, [
               'Documento' => $data['Documento'],
               'Rol' => $data['Rol']
            ]);
         }

         $empleado->update([
            "Nombre" => $data['Nombre'],
            "Apellidos" => $data['Apellidos'],
            "Correo" => $data['Correo'],
            "Telefono" => $data['Telefono'],
            "FechaNacimiento" => $data['FechaNacimiento'],
            "LugarNacimiento" => $data['LugarNacimiento'],
            "TipoDocumento" => $data['TipoDocumento'],
            "Documento" => $data['Documento'],
            "Sexo" => $data['Sexo'],
            "DireccionResidencia" => $data['DireccionResidencia'],
            "CiudadResidencia" => $data['CiudadResidencia'],
            "EstadoCivil" => $data['EstadoCivil'],
            "Cargo" => $data['Cargo'],
            'Estado' => $data['Estado'],
         ]);
      });
   }

   public function delete(int $id): void
   {
      DB::transaction(function () use ($id) {

         $empleado = Empleado::findOrFail($id);

         if ($empleado->imagen) {
            $oldPath = ltrim(str_replace('/storage/', '', $empleado->imagen), '/');

            if (Storage::disk('public')->exists($oldPath)) {
               Storage::disk('public')->delete($oldPath);
            }
         }

         $this->usuarioService->deleteUser($empleado->user_id);
         $empleado->delete();
      });
   }

   public function deleteImagen(int $id): void
   {
      $empleado = Empleado::findOrFail($id);

      if ($empleado->imagen) {
         $oldPath = ltrim(str_replace('/storage/', '', $empleado->imagen), '/');

         if (Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
         }
      }

      $empleado->imagen = null;
      $empleado->save();
   }
}
