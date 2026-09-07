<?php

namespace App\Http\Requests\Noticia;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateNoticiaRequest extends ApiFormRequest
{

    public function authorize(): bool
    {
        $user = Auth::user();
        return $user && ($user->Rol == 0 || $user->Rol == 1);
    }

    public function rules(): array
    {
        return [
            'Titulo' => ['required'],
            'Descripcion' => ['required'],
            'Vencimiento' => ['required'],
            'Destinatario' => ['required'],
            'Hora' => ['required'],
            'Tipo' => ['required'],
            'Correo' => ['required'],
            'Push' => ['required'],
            'Fecha' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            "Titulo.required" => "El titulo es requerido",
            "Descripcion.required" => "La descripción es requerida",
            "Vencimiento.required" => "La fecha de vencimiento es requerida",
            "Destinatario.required" => "El destinatario es requerido",
            "Hora.required" => "La hora es requerida",
            "Tipo.required" => "El tipo es requerido",
            "Fecha.required" => "La fecha es obligatoria",
        ];
    }
}
