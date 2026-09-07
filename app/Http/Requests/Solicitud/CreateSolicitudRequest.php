<?php

namespace App\Http\Requests\Solicitud;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateSolicitudRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();
        return $user && ($user->Rol == 2 || $user->Rol == 3 || $user->Rol == 5);
    }

    public function rules(): array
    {
        return [
            'Descripcion' => ['required'],
            'Tipo' => ['required'],
            'user_id' => ['required', Rule::exists('users', 'id'),],
        ];
    }

    public function messages(): array
    {
        return [
            'Descripcion.required' => 'El campo descripcion es obligatorio.',
            'Tipo.required' => 'El campo tipo es obligatorio.',
            'user_id.required' => 'El campo user es obligatorio.',
            'user_id.exists' => 'El user proporcionado no existe.',
        ];
    }
}
