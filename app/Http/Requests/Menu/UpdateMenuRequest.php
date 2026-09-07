<?php

namespace App\Http\Requests\Menu;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateMenuRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();
        return $user && $user->Rol == 0;
    }

    public function rules(): array
    {
        $id = $this->route('id');
        return [
            'Name' => ['required', Rule::unique('menus', 'Name')->ignore($id)],
            'Type' => ['required'],
            'Icon' => ['required'],
            'Route' => ['required'],
            'Color' => ['required'],
            'Estado' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'Name.required' => 'El nombre es obligatorio.',
            'Name.unique' => 'Ya existe un menu con este nombre.',
            'Type.required' => 'El tipo es obligatorio.',
            'Icon.required' => 'El icono es obligatorio.',
            'Route.required' => 'La ruta es obligatoria.',
            'Color.required' => 'El color es obligatorio.',
        ];
    }
}
