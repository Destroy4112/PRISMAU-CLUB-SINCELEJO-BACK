<?php

namespace App\Http\Requests\Adherente;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateStateAdherenteRequest extends ApiFormRequest
{

    public function authorize(): bool
    {
        $user = Auth::user();
        return $user && ($user->Rol == 0 || $user->Rol == 1);
    }

    public function rules(): array
    {
        return [
            'Estado' => ['required', 'string'],
            'Motivo' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'Estado.required' => 'El Estado es obligatorio.',
            'Motivo.required' => 'El Motivo es obligatorio.',
        ];
    }
}
