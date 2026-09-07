<?php

namespace App\Services;

use App\Models\Menu;
use Illuminate\Support\Collection;

class MenuService
{

   public function create(array $request): void
   {
      Menu::create([
         'Name'    => $request['Name'],
         'Type'    => $request['Type'],
         'Route'   => $request['Route'],
         'Icon'    => $request['Icon'],
         'Color'   => $request['Color'],
         'Estado'  => 1,
      ]);
   }

   public function getAll(): Collection
   {
      return Menu::all();
   }

   public function update(array $request, int $id): void
   {
      $menu = Menu::findOrFail($id);
      $menu->update([
         'Name'    => $request['Name'],
         'Type'    => $request['Type'],
         'Route'   => $request['Route'],
         'Icon'    => $request['Icon'],
         'Color'   => $request['Color'],
         'Estado'  => $request['Estado'],
      ]);
   }

   public function delete(int $id): void
   {
      $menu = Menu::findOrFail($id);
      $menu->delete();
   }
}
