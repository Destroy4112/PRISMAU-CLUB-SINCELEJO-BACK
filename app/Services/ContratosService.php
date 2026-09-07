<?php

namespace App\Services;

use App\Models\Contratos;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ContratosService
{

   public function create(array $request): void
   {
      Contratos::create([
         'Nombres'        => $request['Nombres'],
         'Apellidos'      => $request['Apellidos'],
         'Identificacion' => $request['Identificacion'],
         'Correo'         => $request['Correo'],
         'Telefono'       => $request['Telefono'],
         'Empresa'        => $request['Empresa'],
         'Ciudad'         => $request['Ciudad'],
         'Estado'         => 1,
      ]);
   }

   public function get(array $filters): LengthAwarePaginator
   {
      $limit = $filters['limit'] ?? 30;
      $search = isset($filters['search']) ? trim($filters['search']) : null;
      $searchTerms = $search ? preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) : [];

      $query = Contratos::query()
         ->when(!empty($searchTerms), function ($query) use ($searchTerms) {
            foreach ($searchTerms as $term) {
               $query->where(function ($q) use ($term) {
                  $q->whereRaw("CONCAT(Nombres, ' ', Apellidos) LIKE ?", ["%{$term}%"])
                     ->orWhere('Nombres', 'LIKE', "%{$term}%")
                     ->orWhere('Apellidos', 'LIKE', "%{$term}%")
                     ->orWhere('Identificacion', 'LIKE', "%{$term}%")
                     ->orWhere('Empresa', 'LIKE', "%{$term}%")
                     ->orWhere('Ciudad', 'LIKE', "%{$term}%");
               });
            }
         });
      return $query->orderBy('created_at', 'desc')->paginate($limit);
   }
}
