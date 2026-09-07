<?php

namespace App\Http\Requests\Mensualidad;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GeneratePreferenceRequest extends ApiFormRequest
{
   protected function prepareForValidation(): void
   {
      $this->merge(['valor_diferente' => filter_var($this->valor_diferente, FILTER_VALIDATE_BOOLEAN)]);
   }

   public function authorize(): bool
   {
      // $user = Auth::user();
      // return $user && ($user->Rol == 0 || $user->Rol == 1);
      return true;
   }

   public function rules(): array
   {
      return [
         'mensualidad_id' => ['required', Rule::exists('mensualidades', 'id')],
         'valor_diferente' => ['required', 'boolean'],
         'valor' => ['required', 'numeric', 'min:0'],
      ];
   }

   public function messages(): array
   {
      return [
         'mensualidad_id.required' => 'La mensualidad es requerida.',
         'mensualidad_id.exists' => 'La mensualidad no existe.',
         'valor_diferente.required' => 'El campo valor_diferente es requerido.',
         'valor_diferente.boolean' => 'El campo valor_diferente debe ser un booleano.',
         'valor.required' => 'El campo valor es requerido.',
         'valor.numeric' => 'El campo valor debe ser numérico.',
         'valor.min' => 'El campo valor debe ser mayor o igual a 0.',
      ];
   }
}
