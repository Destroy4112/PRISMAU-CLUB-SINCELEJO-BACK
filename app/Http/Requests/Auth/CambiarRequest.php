<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class CambiarRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'Documento' => ['required', Rule::exists('users', 'Documento')],
            'code' => ['required'],
            'new_password' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'Document.required' => 'El documento es obligatorio.',
            'Document.exists' => 'El usuario no existe.',
            'code.required' => 'El Código es obligatorio.',
            'new_password.required' => 'La Clave es obligatoria.',
        ];
    }
}
