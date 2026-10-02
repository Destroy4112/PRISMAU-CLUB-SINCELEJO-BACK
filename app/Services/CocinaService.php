<?php

namespace App\Services;

use App\Models\Cocina;
use Illuminate\Support\Facades\Validator;

class CocinaService
{
    public function validarCocina(array $data, $id = null)
    {
        $rules = [
            'nombre' => 'required|unique:cocinas,nombre,' . $id,
            'estado' => 'required',
        ];

        $messages = [
            'nombre.required' => 'El campo Nombre es obligatorio.',
            'nombre.unique' => 'El Nombre ya está registrado en el sistema.',
            'estado.required' => 'El campo Estado es obligatorio.',
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            return [
                'status' => false,
                'errors' => $validator->errors()->all()
            ];
        }

        return [
            'status' => true,
            'message' => 'Validación exitosa'
        ];
    }

    public function validarCocinero(array $data)
    {
        $rules = [
            'empleado_id' => 'required|exists:empleados,id',
        ];

        $messages = [
            'empleado_id.required' => 'El campo empleado_id es obligatorio.',
            'empleado_id.exists' => 'El empleado no existe en el sistema.',
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            return [
                'status' => false,
                'errors' => $validator->errors()->all()
            ];
        }

        return [
            'status' => true,
            'message' => 'Validación exitosa'
        ];
    }

    public function crearCocina(array $data)
    {
        $validacion = $this->validarCocina($data);
        if (!$validacion['status']) return $validacion;
        try {
            $cocina = Cocina::create($data);
            return [
                'status' => true,
                'message' => 'Cocina creada correctamente',
                'data' => $cocina
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al crear la cocina',
                'error' => $e->getMessage()
            ];
        }
    }

    public function obtenerCocinas()
    {
        return Cocina::with('empleado')->get();
    }

    public function actualizarCocina(array $data, $id)
    {
        $validate = $this->validarCocina($data, $id);
        if (!$validate['status']) return $validate;
        try {
            $cocina = Cocina::findOrFail($id);
            $cocina->update($data);
            return [
                'status' => true,
                'message' => 'Cocina actualizada correctamente',
                'data' => $cocina
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al actualizar la cocina',
                'error' => $e->getMessage()
            ];
        }
    }

    public function asignCocinero(array $data, $id)
    {
        $validate = $this->validarCocinero($data);
        if (!$validate['status']) return $validate;
        try {
            $cocina = Cocina::findOrFail($id);
            $cocina->update(['empleado_id' => $data['empleado_id']]);
            return [
                'status' => true,
                'message' => 'Cocinero asignado correctamente',
                'data' => $cocina
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al actualizar la cocina',
                'error' => $e->getMessage()
            ];
        }
    }

    public function eliminarCocina($id)
    {
        try {
            $cocina = Cocina::findOrFail($id);
            $cocina->delete();
            return [
                'status' => true,
                'message' => 'Cocina eliminada correctamente'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al eliminar la cocina',
                'error' => $e->getMessage()
            ];
        }
    }
}
