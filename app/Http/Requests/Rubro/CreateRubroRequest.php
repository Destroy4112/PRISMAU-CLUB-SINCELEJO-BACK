<?php

namespace App\Http\Requests\Rubro;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateRubroRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();
        return $user && $user->Rol == 0;
    }

    public function rules(): array
    {
        return [
            'rubro' => ['required', Rule::unique('rubros', 'rubro')],
            'valor' => ['required', 'numeric', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'rubro.required' => 'El rubro es requerido.',
            'rubro.unique' => 'El rubro ya existe.',
            'valor.required' => 'El valor es requerido.',
            'valor.numeric' => 'El valor debe ser numérico.',
            'valor.min' => 'El valor debe ser mayor o igual a 1.',
        ];
    }
}
