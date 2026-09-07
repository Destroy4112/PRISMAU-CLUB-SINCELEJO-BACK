<?php

namespace App\Http\Requests\Invitado;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Support\Facades\Auth;

class CreateInvitadoRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();
        return $user && in_array($user->Rol, [2, 3, 5]);
    }

    public function rules(): array
    {
        return [
            'Nombre' => ['required'],
            'Apellidos' => ['required'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'TipoDocumento' => ['required'],
            'Documento' => ['required'],
            'Telefono' => ['required'],
        ];
    }

    public function messages(): array {
        return [
            'Nombre.required' => 'El campo Nombre es obligatorio.',
            'Apellidos.required' => 'El campo Apellidos es obligatorio.',
            'user_id.required' => 'El campo user_id es obligatorio.',
            'user_id.exists' => 'El user_id proporcionado no existe.',
            'TipoDocumento.required' => 'El campo Tipo Documento es obligatorio.',
            'Documento.required' => 'El campo Documento es obligatorio.',
            'Telefono.required' => 'El campo Telefono es obligatorio.',
        ];
    }
}
