<?php

namespace App\Http\Requests\CuotaBaile;

use App\Http\Requests\ApiFormRequest;
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
         'cuotas_baile_id' => ['required', Rule::exists('cuotas_bailes', 'id')],
         'valor_diferente' => ['required', 'boolean'],
         'valor' => ['required', 'numeric', 'min:0'],
      ];
   }

   public function messages(): array
   {
      return [
         'cuotas_baile_id.required' => 'La cuota es requerida.',
         'cuotas_baile_id.exists' => 'La cuota no existe.',
         'valor_diferente.required' => 'El campo valor_diferente es requerido.',
         'valor_diferente.boolean' => 'El campo valor_diferente debe ser un booleano.',
         'valor.required' => 'El campo valor es requerido.',
         'valor.numeric' => 'El campo valor debe ser numérico.',
         'valor.min' => 'El campo valor debe ser mayor o igual a 0.',
      ];
   }
}
