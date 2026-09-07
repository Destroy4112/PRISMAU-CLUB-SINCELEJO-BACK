<?php

namespace App\Http\Requests\Solicitud;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Support\Facades\Auth;

class ReplySolicitudRequest extends ApiFormRequest
{

    public function authorize(): bool
    {
        $user = Auth::user();
        return $user && ($user->Rol == 0 || $user->Rol == 1);
    }

    public function rules(): array
    {
        return [
            'Respuesta' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'Respuesta.required' => 'La respuesta es obligatoria.',
        ];
    }
}
