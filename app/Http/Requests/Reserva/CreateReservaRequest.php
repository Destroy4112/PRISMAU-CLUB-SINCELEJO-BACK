<?php

namespace App\Http\Requests\Reserva;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateReservaRequest extends ApiFormRequest
{

   public function authorize(): bool
   {
      $user = Auth::user();
      return $user && ($user->Rol == 2 || $user->Rol == 3 || $user->Rol == 5);
   }

   public function rules(): array
   {
      return [
         'user_id' => ['required', Rule::exists('users', 'id')],
         'espacio_id' => ['required', Rule::exists('espacios', 'id')],
         'Fecha' => ['required', 'date'],
         'Inicio' => ['required', 'date_format:H:i'],
         'Fin' => ['required', 'date_format:H:i', 'after:Inicio'],
      ];
   }

   public function messages(): array
   {
      return [
         'user_id.required' => 'El Usuario es obligatorio.',
         'user_id.exists' => 'El Usuario no existe.',
         'espacio_id.required' => 'El Espacio es obligatorio.',
         'espacio_id.exists' => 'El Espacio no existe.',
         'Fecha.required' => 'La Fecha es obligatorio.',
         'Fecha.date' => 'La Fecha no tiene un formato valido.',
         'Inicio.required' => 'La Hora de Inicio es obligatorio.',
         'Inicio.date_format' => 'La Hora de Inicio no tiene un formato valido.',
         'Fin.required' => 'La Hora de Fin es obligatorio.',
         'Fin.date_format' => 'La Hora de Fin no tiene un formato valido.',
         'Fin.after' => 'La Hora de Fin debe ser mayor que la Hora de Inicio.',
      ];
   }
}
