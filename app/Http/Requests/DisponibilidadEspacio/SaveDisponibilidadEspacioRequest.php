<?php

namespace App\Http\Requests\DisponibilidadEspacio;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SaveDisponibilidadEspacioRequest extends ApiFormRequest
{

    public function authorize(): bool
    {
        $user = Auth::user();
        return $user && ($user->Rol == 0 || $user->Rol == 1);
    }

    public function rules(): array
    {
        return [
            'espacio_id' => ['required', 'integer', Rule::exists('espacios', 'id')],
            'disponibilidades' => ['required', 'array'],
            'disponibilidades.*.id' => ['nullable', 'integer', Rule::exists('disponibilidad_espacios', 'id'),],
            'disponibilidades.*.Dia' => [
                'required',
                'string',
                'distinct',
                Rule::in(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo']),
            ],

            'disponibilidades.*.Inicio' => ['required',],
            'disponibilidades.*.Fin' => ['required', 'after:disponibilidades.*.Inicio',],
        ];
    }

    public function messages(): array
    {
        return [
            'espacio_id.required' => 'El espacio es obligatorio.',
            'espacio_id.exists' => 'El espacio seleccionado no existe.',
            'disponibilidades.required' => 'Debe enviar la disponibilidad semanal.',
            'disponibilidades.array' => 'Las disponibilidades deben enviarse como una lista.',
            'disponibilidades.max' => 'Solo puede configurar los siete días de la semana.',
            'disponibilidades.*.Dia.required' => 'El día es obligatorio.',
            'disponibilidades.*.Dia.distinct' => 'No puede enviar más de un horario para el mismo día.',
            'disponibilidades.*.Dia.in' => 'El día seleccionado no es válido.',
            'disponibilidades.*.Inicio.required' => 'La hora inicial es obligatoria.',
            'disponibilidades.*.Fin.required' => 'La hora final es obligatoria.',
            'disponibilidades.*.Fin.after' => 'La hora final debe ser posterior a la hora inicial.',
        ];
    }
}
