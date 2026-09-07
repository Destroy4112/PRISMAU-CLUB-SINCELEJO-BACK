<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class LoginRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'Documento' => ['required', Rule::exists('users', 'Documento')],
            'password' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'Document.required' => 'El documento es obligatorio.',
            'Document.exists' => 'El usuario no existe.',
            'password.required' => 'La clave es obligatoria.',
        ];
    }
}
