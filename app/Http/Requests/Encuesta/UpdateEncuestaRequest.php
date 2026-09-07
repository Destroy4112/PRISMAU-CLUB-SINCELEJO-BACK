<?php

namespace App\Http\Requests\Encuesta;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateEncuestaRequest extends ApiFormRequest
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
            'Titulo' => ['required', Rule::unique('encuestas', 'Titulo')->ignore($id)],
            'Descripcion' => ['required'],
            'Estado' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'Titulo.required' => 'El campo titulo es obligatorio.',
            'Titulo.unique' => 'Ya existe una encuesta con ese titulo.',
            'Descripcion.required' => 'El campo descripción es obligatorio.',
        ];
    }
}
