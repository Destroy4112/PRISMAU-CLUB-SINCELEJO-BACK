<?php

namespace App\Http\Requests\Familiar;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateImageFamiliarRequest extends ApiFormRequest
{

    public function authorize(): bool
    {
        $user = Auth::user();
        return $user && ($user->Rol == 0 || $user->Rol == 1);
    }

    public function rules(): array
    {
        return [
            'imagen' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }

    public function messages()
    {
        return [
            'imagen.required' => 'Seleccione una imagen para actualizar.',
            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.mimes' => 'El archivo debe ser una imagen en formato JPEG, PNG, JPG o GIF.',
            'imagen.max' => 'El archivo debe tener un tamaño máximo de 2MB.',
        ];
    }
}
