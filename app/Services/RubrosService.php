<?php

namespace App\Services;

use App\Models\Rubros;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RubrosService
{

   public function create(array $data): void
   {
      Rubros::create($data);
   }

   public function getAll(): Collection
   {
      return Rubros::all();
   }

   public function getPaginated(array $filters): LengthAwarePaginator
   {
      $limit = min(max((int) ($filters['limit'] ?? 30), 1), 100);
      $search = trim((string) ($filters['search'] ?? ''));

      return Rubros::query()
         ->when($search !== '', function (Builder $query) use ($search) {
            $query->where('rubro', 'like', "%{$search}%");
         })
         ->orderByDesc('created_at')
         ->paginate($limit);
   }

   public function get(int $id): Rubros
   {
      return Rubros::findOrFail($id);
   }

   public function update(array $data, Rubros $rubro): void
   {
      $rubro->update($data);
   }

   public function delete(int $id): void
   {
      $rubro = $this->get($id);
      $rubro->delete();
   }
}
