<?php

namespace App\Services;

use App\Models\Menu_Role;
use App\Models\Rol;
use Illuminate\Support\Collection;

class MenuRolService
{

   public function assign(array $data): void
   {
      Menu_Role::create([
         'menu_id' => $data['menu_id'],
         'role_id' => $data['role_id'],
      ]);
   }

   public function getByRole(int $id): Collection
   {
      $menusRol = Menu_Role::with('menu')->where('role_id', $id)->get();
      return $menusRol->map(function ($menuRol) {
         return array_merge(
            $menuRol->menu->toArray(),
            ['menuRolId' => $menuRol->id]
         );
      })->values();
   }

   public function getByMenuType(int $id): Collection
   {
      $rol = Rol::findOrFail($id);
      $menus = $rol->menus()->get();

      return $menus->groupBy('Type');
   }

   public function delete(int $id): void
   {
      $menuRole = Menu_Role::findOrFail($id);
      $menuRole->delete();
   }
}
