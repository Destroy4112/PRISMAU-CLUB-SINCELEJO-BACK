<?php

namespace App\Http\Requests\Admins;

use App\Http\Requests\ApiFormRequest;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateAdminRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();
        return $user && $user->Rol == 0;
    }

    public function rules(): array
    {
        $adminId = (int) $this->route('id');
        $admin = Admin::findOrFail($adminId);
        $userId = $admin->user_id;

        return [
            'Nombre' => ['required'],
            'Apellidos' => ['required'],
            'user.Documento' => ['required', Rule::unique('users', 'Documento')->ignore($userId)],
            'Correo' => ['required', 'email', Rule::unique('admins', 'Correo')->ignore($adminId)],
            'Telefono' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'Nombre.required' => 'El Nombre es obligatorio.',
            'Apellidos.required' => 'Los Apellidos son obligatorio.',
            'user.Documento.required' => 'El Documento es obligatorio.',
            'user.Documento.unique' => 'El Documento ya esta registrado en el sistema.',
            'Correo.required' => 'El campo Correo es obligatorio.',
            'Correo.email' => 'El Correo no tiene un formato valido.',
            'Correo.unique' => 'El Correo ya esta registrado en el sistema.',
            'Telefono.required' => 'El Telefono es obligatorio.',
        ];
    }
}
