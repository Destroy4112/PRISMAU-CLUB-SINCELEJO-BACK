<?php

namespace App\Http\Requests\Pregunta;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreatePreguntaRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();
        return $user && $user->Rol == 0;
    }

    public function rules(): array
    {
        return [
            'Pregunta' => ['required'],
            'encuesta_id' => ['required', Rule::exists('encuestas', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'Pregunta.required' => 'La pregunta es requerida',
            'encuesta_id.required' => 'La encuesta es requerida',
            'encuesta_id.exists' => 'La encuesta no existe',
        ];
    }
}
