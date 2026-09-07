<?php

namespace App\Http\Requests\Respuesta;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateRespuestaRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();
        return $user && $user->Rol == 0;
    }

    public function rules(): array
    {
        return [
            'Respuesta' => ['required'],
            'pregunta_id' => ['required', Rule::exists('preguntas', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'Respuesta.required' => 'La respuesta es requerida',
            'pregunta_id.required' => 'La pregunta es requerida',
            'pregunta_id.exists' => 'La pregunta no existe',
        ];
    }
}
