<?php

namespace App\Http\Requests\Espacio;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateEspacioRequest extends ApiFormRequest
{

    public function authorize(): bool
    {
        $user = Auth::user();
        return $user && ($user->Rol == 0 || $user->Rol == 1);
    }

    public function rules(): array
    {
        return [
            'Descripcion' => ['required'],
            'imagen' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'Estado' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'Descripcion.required' => 'La descripción es obligatoria.',
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.mimes' => 'El archivo debe ser una imagen en formato JPEG, PNG, JPG o GIF.',
            'imagen.max' => 'El archivo debe tener un tamaño máximo de 2MB.',
            'Estado.required' => 'El estado es obligatorio.',
        ];
    }
}
