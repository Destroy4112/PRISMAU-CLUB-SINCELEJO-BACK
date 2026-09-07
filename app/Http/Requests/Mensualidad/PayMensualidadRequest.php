<?php

namespace App\Http\Requests\Mensualidad;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PayMensualidadRequest extends ApiFormRequest
{

    protected function prepareForValidation(): void
    {
        $this->merge(['valor_diferente' => filter_var($this->valor_diferente, FILTER_VALIDATE_BOOLEAN)]);
    }

    public function authorize(): bool
    {
        $user = Auth::user();
        return $user && ($user->Rol == 0 || $user->Rol == 1);
    }

    public function rules(): array
    {
        return [
            'mensualidad_id' => ['required', Rule::exists('mensualidades', 'id')],
            'metodo_pago' => ['required'],
            'referencia_pago' => ['required'],
            'valor_diferente' => ['required', 'boolean'],
            'soporte' => ['required', 'file', 'mimes:jpg,png,pdf', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'mensualidad_id.required' => 'La mensualidad es obligatoria.',
            'mensualidad_id.exists' => 'La mensualidad no existe.',
            'metodo_pago.required' => 'El método de pago es obligatorio.',
            'referencia_pago.required' => 'La referencia de pago es obligatoria.',
            'valor_diferente.required' => 'El check es obligatorio.',
            'soporte.required' => 'El soporte es obligatorio.',
            'soporte.mimes' => 'El soporte debe ser un archivo de tipo jpg, png o pdf.',
            'soporte.max' => 'El soporte debe ser menor a 5MB',
        ];
    }
}
