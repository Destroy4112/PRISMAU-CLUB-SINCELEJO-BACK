<?php

namespace App\Http\Requests\Invitado;

use App\Http\Requests\ApiFormRequest;

class SaveImageRequest extends ApiFormRequest
{
   public function authorize(): bool
   {
      return true;
   }

   public function rules(): array
   {
      return [
         'imagen' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:4096'],
      ];
   }

   public function messages()
   {
      return [
         'imagen.required' => 'Seleccione una imagen para actualizar.',
         'imagen.image' => 'El archivo debe ser una imagen.',
         'imagen.mimes' => 'El archivo debe ser una imagen en formato JPEG, PNG, JPG o GIF.',
         'imagen.max' => 'El archivo debe tener un tamaño máximo de 4MB.',
      ];
   }
}
