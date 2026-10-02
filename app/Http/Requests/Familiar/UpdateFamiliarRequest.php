<?php

namespace App\Http\Requests\Familiar;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateFamiliarRequest extends ApiFormRequest
{
   public function authorize(): bool
   {
      $user = Auth::user();
      return $user && ($user->Rol == 0 || $user->Rol == 1);
   }

   public function rules(): array
   {
      $familiar = $this->route('familiar');
      return [
         'asociado_id' => [
            'bail',
            'nullable',
            'required_without:adherente_id',
            'prohibits:adherente_id',
            'integer',
            Rule::exists('asociados', 'id'),
         ],
         'adherente_id' => [
            'bail',
            'nullable',
            'required_without:asociado_id',
            'prohibits:asociado_id',
            'integer',
            Rule::exists('adherentes', 'id'),
         ],
         'Nombre' => ['required'],
         'Apellidos' => ['required'],
         'TipoDocumento' => ['required'],
         'Documento' => ['required', Rule::unique('users', 'Documento')->ignore($familiar->user_id)],
         'Sexo' => ['required'],
         'Codigo' => ['nullable'],
         'Parentesco' => ['required'],
         'Correo' => ['nullable', 'email'],
         'Telefono' => ['nullable'],
         'FechaNacimiento' => ['nullable'],
         'LugarNacimiento' => ['nullable'],
         'DireccionResidencia' => ['nullable'],
         'CiudadResidencia' => ['nullable'],
         'EstadoCivil' => ['nullable'],
         'Estado' => ['required'],
      ];
   }

   public function messages(): array
   {
      return [
         'asociado_id.required_without' => 'Debe seleccionar un asociado o un adherente.',
         'adherente_id.required_without' => 'Debe seleccionar un asociado o un adherente.',
         'asociado_id.prohibits' => 'El familiar debe pertenecer a un asociado o a un adherente, no a ambos.',
         'adherente_id.prohibits' => 'El familiar debe pertenecer a un asociado o a un adherente, no a ambos.',
         'asociado_id.integer' => 'El ID del asociado debe ser un número entero.',
         'adherente_id.integer' => 'El ID del adherente debe ser un número entero.',
         'asociado_id.exists' => 'El asociado seleccionado no existe.',
         'adherente_id.exists' => 'El adherente seleccionado no existe.',
         'Nombre.required' => 'El Nombre es obligatorio.',
         'Apellidos.required' => 'Los Apellidos son obligatorio.',
         'TipoDocumento.required' => 'El Tipo Documento es obligatorio.',
         'Documento.required' => 'El Documento es obligatorio.',
         'Documento.unique' => 'El Documento ya esta registrado en el sistema.',
         'Sexo.required' => 'El Sexo es obligatorio.',
         'Parentesco.required' => 'El Parentesco es obligatorio.',
         'Correo.email' => 'El Correo no tiene un formato valido.',
         'Codigo.required' => 'El Codigo es obligatorio.',
         'Estado.required' => 'El Estado es obligatorio.',
      ];
   }
}
