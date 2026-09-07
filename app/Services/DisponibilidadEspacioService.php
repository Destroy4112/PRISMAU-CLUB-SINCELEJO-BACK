<?php

namespace App\Services;

use App\Models\DisponibilidadEspacio;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DisponibilidadEspacioService
{

   private function validarHorarios(array $disponibilidades): void
   {
      foreach ($disponibilidades as $index => $item) {
         if ($item['Inicio'] >= $item['Fin']) {
            throw ValidationException::withMessages([
               "disponibilidades.{$index}.Fin" =>
               "En {$item['Dia']}, la hora final debe ser posterior a la hora inicial.",
            ]);
         }
      }
   }

   public function save(int $espacioId, array $disponibilidades): void
   {
      DB::transaction(function () use ($espacioId, $disponibilidades) {
         $this->validarHorarios($disponibilidades);

         $diasRecibidos = collect($disponibilidades)->pluck('Dia')->values()->all();

         DisponibilidadEspacio::query()->where('espacio_id', $espacioId)
            ->when(
               count($diasRecibidos) > 0,
               fn($query) =>
               $query->whereNotIn('Dia', $diasRecibidos),
               fn($query) => $query
            )
            ->delete();

         foreach ($disponibilidades as $item) {
            DisponibilidadEspacio::query()->updateOrCreate(
               [
                  'espacio_id' => $espacioId,
                  'Dia' => $item['Dia'],
               ],
               [
                  'Inicio' => $item['Inicio'],
                  'Fin' => $item['Fin'],
               ]
            );
         }
      });
   }

   public function getByEspacio(int $espacioId): Collection
   {
      return DisponibilidadEspacio::where('espacio_id', $espacioId)
         ->orderByRaw("FIELD(Dia, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo')")
         ->orderBy('Inicio', 'asc')->get();
   }

   public function consultarDisponibilidad(int $espacioId, string $dia, string $horaInicio, string $horaFin): bool
   {
      return DisponibilidadEspacio::where('espacio_id', $espacioId)
         ->where('Dia', $dia)
         ->where('Inicio', '<=', $horaInicio)
         ->where('Fin', '>=', $horaFin)
         ->exists();
   }
}
