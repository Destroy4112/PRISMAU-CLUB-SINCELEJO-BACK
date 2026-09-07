<?php

namespace App\Http\Requests\Usuario;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Support\Facades\Auth;

class ChangePasswordRequest extends ApiFormRequest
{
   public function authorize(): bool
   {
      $user = Auth::user();
      return $user && in_array($user->Rol, [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]);
   }

   public function rules(): array
   {
      return [
         'password' => ['required'],
      ];
   }

   public function messages(): array
   {
      return [
         'password.required' => 'La contraseña es requerida',
      ];
   }
}
