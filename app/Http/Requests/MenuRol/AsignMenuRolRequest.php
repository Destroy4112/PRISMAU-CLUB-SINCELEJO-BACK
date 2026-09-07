<?php

namespace App\Http\Requests\MenuRol;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AsignMenuRolRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();
        return $user && $user->Rol == 0;
    }

    public function rules(): array
    {
        return [
            'menu_id' => [
                'required',
                'integer',
                Rule::exists('menus', 'id'),
                Rule::unique('menu_role', 'menu_id')->where(fn($query) => $query->where('role_id', $this->input('role_id'))),
            ],
            'role_id' => ['required', Rule::exists('roles', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'menu_id.required' => 'El campo menu es requerido.',
            'menu_id.exists' => 'El menu no existe en la base de datos.',
            'role_id.required' => 'El campo rol es requerido.',
            'role_id.exists' => 'El rol no existe en la base de datos.',
            'menu_id.unique' => 'El menu ya esta asignado al rol.',
        ];
    }
}
