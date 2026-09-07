<?php

namespace App\Http\Requests\Empleado;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CreateEmpleadoRequest extends ApiFormRequest
{

    public function authorize(): bool
    {
        $user = Auth::user();
        return $user && ($user->Rol == 0 || $user->Rol == 1);
    }

    public function rules(): array
    {
        return [
            'Nombre' => ['required'],
            'Apellidos' => ['required'],
            'Correo' => ['required', 'email'],
            'Telefono' => ['required'],
            'FechaNacimiento' => ['required'],
            'LugarNacimiento' => ['required'],
            'TipoDocumento' => ['required'],
            'Documento' => ['required', Rule::unique('users', 'Documento')],
            'Sexo' => ['required'],
            'DireccionResidencia' => ['required'],
            'CiudadResidencia' => ['required'],
            'EstadoCivil' => ['required'],
            'Cargo' => ['required'],
            'Estado' => ['required'],
            'Rol' => ['required'],
        ];
    }

    public function messages():array{
        return[
            'Nombre.required' => 'El Nombre es obligatorio.',
            'Apellidos.required' => 'Los Apellidos son obligatorio.',
            'Correo.required' => 'El Correo es obligatorio.',
            'Correo.email' => 'El Correo no tiene un formato valido.',
            'Telefono.required' => 'El Telefono es obligatorio.',
            'FechaNacimiento.required' => 'La Fecha Nacimiento es obligatorio.',
            'LugarNacimiento.required' => 'El Lugar Nacimiento es obligatorio.',
            'TipoDocumento.required' => 'El Tipo Documento es obligatorio.',
            'Documento.required' => 'El Documento es obligatorio.',
            'Documento.unique' => 'El Documento ya esta registrado en el sistema.',
            'Sexo.required' => 'El Sexo es obligatorio.',
            'DireccionResidencia.required' => 'La Direccion Residencia es obligatorio.',
            'CiudadResidencia.required' => 'La Ciudad Residencia es obligatorio.',
            'EstadoCivil.required' => 'El Estado Civil es obligatorio.',
            'Cargo.required' => 'El Cargo es obligatorio.',
            'Estado.required' => 'El Estado es obligatorio.',
            'Rol.required' => 'El Rol es obligatorio.',
        ];
    }
}
