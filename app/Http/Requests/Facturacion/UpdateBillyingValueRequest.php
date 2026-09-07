<?php

namespace App\Http\Requests\Facturacion;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateBillyingValueRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();
        return $user && ($user->Rol == 0 || $user->Rol == 1);
    }

    public function rules(): array
    {
        return [
            'documento' => ['required', Rule::exists('users', 'Documento')],
            'field' => ['required', 'string', Rule::in(['mensualidad', 'cuotaBaile'])],
            'value' => ['required', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'documento.required' => 'El documento es requerido.',
            'documento.exists' => 'El documento no existe.',
            'field.required' => 'El campo es requerido.',
            'field.string' => 'El campo debe ser una cadena de texto.',
            'field.in' => 'El campo debe ser "mensualidad" o "cuotaBaile".',
            'value.required' => 'El valor es requerido.',
            'value.numeric' => 'El valor debe ser numérico.',
        ];
    }
}
