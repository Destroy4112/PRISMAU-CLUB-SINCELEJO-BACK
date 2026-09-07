<?php

namespace App\Http\Requests\Facturacion;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GenerateBillyingRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();
        return $user && ($user->Rol == 0 || $user->Rol == 1);
    }

    public function rules(): array
    {
        return [
            'rubro_id' => ['required', Rule::exists('rubros', 'id')],
            'anio' => ['required'],
            'isCuota' => ['required', 'boolean'],
            'cuotas' => ['exclude_unless:isCuota,true', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'rubro_id.required' => 'El rubro es requerido.',
            'rubro_id.exists' => 'El rubro no existe.',
            'anio.required' => 'El año es requerido.',
            'isCuota.required' => 'El campo isCuota es requerido.',
            'isCuota.boolean' => 'El campo isCuota debe ser un booleano.',
            'cuotas.required_if' => 'El campo cuotas es requerido.',
            'cuotas.integer' => 'El campo cuotas debe ser un número entero.',
            'cuotas.min' => 'El campo cuotas debe ser mayor o igual a 1.',
        ];
    }
}
