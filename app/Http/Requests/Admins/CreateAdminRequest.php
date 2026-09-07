<?php

namespace App\Http\Requests\Admins;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateAdminRequest extends ApiFormRequest
{

    public function authorize(): bool
    {
        $user = Auth::user();
        return $user && $user->Rol == 0;
    }

    public function rules(): array
    {
        return [
            'Nombre' => ['required'],
            'Apellidos' => ['required'],
            'user.Documento' => ['required', Rule::unique('users', 'Documento')],
            'Correo' => ['required', 'email', Rule::unique('admins', 'Correo')],
            'Telefono' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'Nombre.required' => 'El Nombre es obligatorio.',
            'Apellidos.required' => 'Los Apellidos son obligatorio.',
            'user.Documento.required' => 'El Documento es obligatorio.',
            'user.Documento.unique' => 'El Documento ya esta registrado en el sistema.',
            'Correo.required' => 'El campo Correo es obligatorio.',
            'Correo.email' => 'El Correo no tiene un formato valido.',
            'Correo.unique' => 'El Correo ya esta registrado en el sistema.',
            'Telefono.required' => 'El Telefono es obligatorio.',
        ];
    }
}
