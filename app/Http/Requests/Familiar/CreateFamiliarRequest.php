<?php

namespace App\Http\Requests\Familiar;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateFamiliarRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();
        return $user && ($user->Rol == 0 || $user->Rol == 1);
    }

    public function rules(): array
    {
        return [
            'Nombre' => ['required'],
            'Apellidos' => ['required'],
            'TipoDocumento' => ['required'],
            'Documento' => ['required', Rule::unique('users', 'Documento')],
            'Sexo' => ['required'],
            'Parentesco' => ['required'],
            'Correo' => ['nullable', 'email'],
            'Telefono' => ['nullable'],
            'FechaNacimiento' => ['nullable'],
            'LugarNacimiento' => ['nullable'],
            'DireccionResidencia' => ['nullable'],
            'CiudadResidencia' => ['nullable'],
            'EstadoCivil' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'Nombre.required' => 'El Nombre es obligatorio.',
            'Apellidos.required' => 'Los Apellidos son obligatorio.',
            'TipoDocumento.required' => 'El Tipo Documento es obligatorio.',
            'Documento.required' => 'El Documento es obligatorio.',
            'Documento.unique' => 'El Documento ya esta registrado en el sistema.',
            'Sexo.required' => 'El Sexo es obligatorio.',
            'Parentesco.required' => 'El Parentesco es obligatorio.',
            'Correo.email' => 'El Correo no tiene un formato valido.',
        ];
    }
}
