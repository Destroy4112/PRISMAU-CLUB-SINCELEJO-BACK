<?php

namespace App\Http\Requests\Contrato;

use App\Http\Requests\ApiFormRequest;

class CreateContratoRequest extends ApiFormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'Nombres' => ['required', 'string', 'max:255'],
            'Apellidos' => ['required', 'string', 'max:255'],
            'Identificacion' => ['required', 'string'],
            'Correo' => ['required', 'email', 'max:255'],
            'Telefono' => ['required', 'string', 'max:15'],
            'Empresa' => ['nullable', 'string', 'max:255'],
            'Ciudad' => ['required', 'string', 'max:255'],
            'Estado' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'Nombres.required' => 'El Nombres es obligatorio.',
            'Apellidos.required' => 'Los Apellidos son obligatorio.',
            'Identificacion.required' => 'El Identificacion es obligatorio.',
            'Correo.required' => 'El Correo es obligatorio.',
            'Correo.email' => 'El Correo no es valido.',
            'Telefono.required' => 'El Telefono es obligatorio.',
            'Ciudad.required' => 'La Ciudad es obligatorio.',
        ];
    }
}
