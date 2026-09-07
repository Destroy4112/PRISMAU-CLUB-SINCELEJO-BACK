<?php

namespace App\Services;

use App\Models\Admin;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AdminService
{

   public function __construct(protected UserService $userService) {}

   public function create(array $data): void
   {
      DB::transaction(function () use ($data) {
         $user = $this->userService->createUser([
            'Documento' => $data['user']['Documento'],
            'password'  => $data['user']['password'] ?? null,
            'Rol'       => 1,
         ]);

         Admin::create([
            'user_id'   => $user->id,
            'Nombre'    => $data['Nombre'],
            'Apellidos' => $data['Apellidos'],
            'Correo'    => $data['Correo'],
            'Telefono'  => $data['Telefono'],
            'Estado'    => 1,
         ]);
      });
   }

   public function getAll(array $filters): LengthAwarePaginator
   {
      $limit = $filters['limit'] ?? 30;
      $search = isset($filters['search']) ? trim($filters['search']) : null;
      $searchTerms = $search ? preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) : [];

      return Admin::query()->with('user')->whereHas('user', function ($q) {
         $q->where('Rol', 1);
      })
         ->when(!empty($searchTerms), function ($query) use ($searchTerms) {
            foreach ($searchTerms as $term) {
               $query->where(function ($q) use ($term) {
                  $q->whereRaw("CONCAT(Nombre, ' ', Apellidos) LIKE ?", ["%{$term}%"])
                     ->orWhere('Nombre', 'LIKE', "%{$term}%")
                     ->orWhere('Apellidos', 'LIKE', "%{$term}%")
                     ->orWhere('correo', 'LIKE', "%{$term}%")
                     ->orWhereHas('user', function ($userQuery) use ($term) {
                        $userQuery->where('documento', 'LIKE', "%{$term}%");
                     });
               });
            }
         })
         ->paginate($limit);
   }

   public function update(array $data, int $id): void
   {
      DB::transaction(function () use ($data, $id) {
         $admin = Admin::with('user')->findOrFail($id);

         if (isset($data['user']['Documento']) && $data['user']['Documento'] !== $admin->user->Documento) {
            $this->userService->updateUser($admin->user_id, [
               'Documento' => $data['user']['Documento'],
            ]);
         }

         $admin->update([
            'Nombre'    => $data['Nombre'],
            'Apellidos' => $data['Apellidos'],
            'Correo'    => $data['Correo'],
            'Telefono'  => $data['Telefono'],
         ]);
      });
   }

   public function changeStatus(int $id): void
   {
      $admin = Admin::findOrFail($id);
      $admin->Estado = $admin->Estado === 1 ? 0 : 1;
      $admin->save();
   }

   public function delete(int $id): void
   {
      DB::transaction(function () use ($id) {
         $admin = Admin::findOrFail($id);
         $userId = $admin->user_id;

         $admin->delete();
         $this->userService->deleteUser($userId);
      });
   }
}
