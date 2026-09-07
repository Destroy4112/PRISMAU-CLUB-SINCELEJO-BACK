<?php

namespace App\Services;

use App\Mail\EstadosMail;
use App\Models\Adherente;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class AdherenteService
{

   public function __construct(
      protected UserService $usuarioService,
      protected EstadoService $estadoService,
      protected SocioService $socioService
   ) {}

   public function create(array $data): Adherente
   {
      return DB::transaction(function () use ($data) {

         $user = $this->usuarioService->createUser(['Documento' => $data['Documento'], 'Rol' => 2]);

         return Adherente::create([
            'asociado_id' => $data['asociado_id'],
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
      $adherente = Adherente::findOrFail($id);

      $oldImage = $adherente->imagen;

      $nameImage = Str::slug($adherente->Documento) . '_' . Str::uuid() . '.' . $imagen->getClientOriginalExtension();
      $path = $imagen->storeAs('personal', $nameImage, 'public');

      $adherente->imagen = Storage::url($path);
      $adherente->save();

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

      $query = Adherente::withCount('familiares')
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

   public function adherenteWithFamiliars(int $id): Collection
   {
      return Adherente::with(['familiares' => function ($query) {
         $query->select('id', 'adherente_id', 'Nombre', 'Apellidos', 'parentesco');
      }])->select('id', 'imagen', 'Nombre', 'Apellidos', 'TipoDocumento', 'Documento', 'Estado')->find($id);
   }

   public function update(array $data, Adherente $adherente): void
   {
      DB::transaction(function () use ($data, $adherente) {
         if ($data['Documento'] != $adherente->user->Documento) {
            $this->usuarioService->updateUser($adherente->user->id, [
               'Documento' => $data['Documento'],
            ]);
         }
         if ($adherente->Codigo != $data['Codigo']) {
            $adherente->familiares()->update(['Codigo' => $data['Codigo']]);
         }
         $adherente->update([
            "asociado_id" => $data['asociado_id'],
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
         $adherente = Adherente::findOrFail($id);

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

         $adherente->familiares()->update(['Estado' => $estado]);

         $this->estadoService->create([
            'user_id' => $adherente->user_id,
            'Estado' => $estadoString,
            'Motivo' => $motivo,
         ]);

         $adherente->update(['Estado' => $estado,]);

         DB::afterCommit(function () use ($adherente, $estadoString, $motivo) {
            Mail::to($adherente->Correo)->send(new EstadosMail($estadoString, $motivo));
         });
      });
   }

   public function changeToAsociado(int $id): void
   {
      DB::transaction(function () use ($id) {
         $usuario = $this->usuarioService->getUser($id);
         $usuario->update(['Rol' => 2]);

         $this->socioService->changeAdherenteToAsociado((int)$usuario->id);;
      });
   }

   public function delete(int $id): void
   {
      DB::transaction(function () use ($id) {

         $adherente = Adherente::with('familiares')->findOrFail($id);

         if ($adherente->imagen) {
            $oldPath = ltrim(str_replace('/storage/', '', $adherente->imagen), '/');

            if (Storage::disk('public')->exists($oldPath)) {
               Storage::disk('public')->delete($oldPath);
            }
         }

         foreach ($adherente->familiares as $familiar) {
            if ($familiar->imagen) {
               $familiarImagePath = ltrim(str_replace('/storage/', '', $familiar->imagen), '/');

               if (Storage::disk('public')->exists($familiarImagePath)) {
                  Storage::disk('public')->delete($familiarImagePath);
               }
            }

            $this->usuarioService->deleteUser($familiar->user_id);
            $familiar->delete();
         }

         $this->usuarioService->deleteUser($adherente->user_id);
         $adherente->delete();
      });
   }

   public function deleteImagen(int $id): void
   {
      $adherente = Adherente::findOrFail($id);

      if ($adherente->imagen) {
         $oldPath = ltrim(str_replace('/storage/', '', $adherente->imagen), '/');

         if (Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
         }
      }

      $adherente->imagen = null;
      $adherente->save();
   }
}
