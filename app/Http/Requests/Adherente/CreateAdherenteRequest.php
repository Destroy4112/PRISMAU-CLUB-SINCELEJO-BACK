<?php

namespace App\Http\Requests\Adherente;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateAdherenteRequest extends ApiFormRequest
{

    public function authorize(): bool
    {
        $user = Auth::user();
        return $user && ($user->Rol == 0 || $user->Rol == 1);
    }

    public function rules(): array
    {
        return [
            'asociado_id' => ['required', Rule::exists('asociados', 'id'), Rule::unique('adherentes', 'asociado_id')],
            'Nombre' => ['required'],
            'Apellidos' => ['required'],
            'Codigo' => ['required', Rule::unique('asociados', 'Codigo')],
            'TipoDocumento' => ['required'],
            'Documento' => ['required', Rule::unique('users', 'Documento')],
            'Correo' => ['required', 'email', Rule::unique('asociados', 'Correo')],
            'Telefono' => ['required'],
            'FechaNacimiento' => ['nullable'],
            'LugarNacimiento' => ['nullable'],
            'Sexo' => ['required'],
            'DireccionResidencia' => ['nullable'],
            'CiudadResidencia' => ['nullable'],
            'TiempoResidencia' => ['nullable'],
            'EstadoCivil' => ['nullable'],
            'Profesion' => ['nullable'],
            'Trabajo' => ['nullable'],
            'Cargo' => ['nullable'],
            'TiempoServicio' => ['nullable'],
            'DireccionOficina' => ['nullable'],
            'TelOficina' => ['nullable'],
            'CiudadOficina' => ['nullable'],
            'Estado' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'asociado_id.required' => 'El Asociado es obligatorio.',
            'asociado_id.exists' => 'El Asociado no existe.',
            'asociado_id.unique' => 'El Asociado ya ha sido asignado.',
            'Nombre.required' => 'El Nombre es obligatorio.',
            'Apellidos.required' => 'Los Apellidos son obligatorio.',
            'TipoDocumento.required' => 'El Tipo Documento es obligatorio.',
            'Documento.required' => 'El Documento es obligatorio.',
            'Documento.unique' => 'El Documento ya está registrado en el sistema.',
            'Correo.required' => 'El Correo es obligatorio.',
            'Correo.email' => 'El Correo no tiene un formato valido.',
            'Correo.unique' => 'El Correo ya está registrado en el sistema.',
            'Codigo.required' => 'El Codigo es obligatorio.',
            'Codigo.unique' => 'El Codigo ya está registrado en el sistema.',
            'Telefono.required' => 'El Telefono es obligatorio.',
            'Sexo.required' => 'El Sexo es obligatorio.',
            'Estado.required' => 'El Estado es obligatorio.',
        ];
    }
}
