<?php

namespace App\Services;

use App\Models\Espacio;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Throwable;

class EspacioService
{

   public function create(array $data, UploadedFile $imagen): void
   {
      $nameImage = Str::slug($data['Descripcion']) . '_' . Str::uuid() . '.' . $imagen->extension();
      $path = $imagen->storeAs('espacios', $nameImage, 'public');
      $url = Storage::url($path);

      try {
         Espacio::create([
            'Descripcion' => $data['Descripcion'],
            'imagen' => $url,
            'Estado' => $data['Estado'],
         ]);
      } catch (Throwable $e) {
         Storage::disk('public')->delete($path);
         throw $e;
      }
   }

   public function getPaginated(array $filters): LengthAwarePaginator
   {
      $limit = $filters['limit'] ?? 30;
      $search = isset($filters['search']) ? trim($filters['search']) : null;
      $searchTerms = $search ? preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) : [];
      $state = $filters['state'] ?? null;

      $query = Espacio::query()
         ->when(!empty($searchTerms), function ($query) use ($searchTerms) {
            foreach ($searchTerms as $term) {
               $query->where(function ($q) use ($term) {
                  $q->where('Descripcion', 'LIKE', "%{$term}%");
               });
            }
         })
         ->when(
            array_key_exists('state', $filters) && $state !== null && $state !== '',
            function ($query) use ($state) {
               $query->where('Estado', (int) $state);
            }
         )
         ->orderBy('Descripcion', 'asc')
         ->paginate($limit);

      return $query;
   }

   public function getAll(): Collection
   {
      return Espacio::all();
   }

   public function update(array $data, ?UploadedFile $imagen, Espacio $espacio): void
   {
      $newPath = null;
      $oldImage = $espacio->imagen;
      try {
         $espacio->Descripcion = $data['Descripcion'];
         $espacio->Estado = $data['Estado'];

         if ($imagen !== null) {
            $nameImage = Str::slug($data['Descripcion']) . '_' . Str::uuid() . '.' . $imagen->extension();
            $newPath = $imagen->storeAs('espacios', $nameImage, 'public');
            $url = Storage::url($newPath);
            $espacio->imagen = $url;
         }

         $espacio->save();

         if ($newPath !== null && $oldImage) {
            $oldPath = ltrim(str_replace('/storage/', '', $oldImage),                '/');
            Storage::disk('public')->delete($oldPath);
         }
      } catch (Throwable $e) {
         if ($newPath !== null) {
            Storage::disk('public')->delete($newPath);
         }
         throw $e;
      }
   }

   public function delete(int $id): void
   {
      $espacio = Espacio::findOrFail($id);
      if ($espacio->imagen) {
         $oldPath = ltrim(str_replace('/storage/', '', $espacio->imagen), '/');

         if (Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
         }
      }
      $espacio->delete();
   }
}
