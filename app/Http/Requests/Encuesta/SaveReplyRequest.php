<?php

namespace App\Http\Requests\Encuesta;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SaveReplyRequest extends ApiFormRequest
{
   public function authorize(): bool
   {
      $user = Auth::user();
      return $user && ($user->Rol == 2 || $user->Rol == 3);
   }

   public function rules(): array
   {
      return [
         'user_id' => ['required', Rule::exists('users', 'id')],
         'respuestas' => ['required', 'array', 'min:1'],
         'respuestas.*.pregunta_id' => ['required', Rule::exists('preguntas', 'id')],
         'respuestas.*.respuesta_id' => ['required', Rule::exists('respuestas', 'id')],
      ];
   }

   public function messages(): array
   {
      return [
         'user_id.required' => 'El usuario es obligatorio.',
         'user_id.exists' => 'El usuario proporcionado no existe.',
         'respuestas.required' => 'Las respuestas son obligatorias.',
         'respuestas.*.pregunta_id.required' => 'La pregunta es obligatoria.',
         'respuestas.*.pregunta_id.exists' => 'La pregunta proporcionada no existe.',
         'respuestas.*.respuesta_id.required' => 'La respuesta es obligatoria.',
         'respuestas.*.respuesta_id.exists' => 'La respuesta proporcionada no existe.',
      ];
   }
}
