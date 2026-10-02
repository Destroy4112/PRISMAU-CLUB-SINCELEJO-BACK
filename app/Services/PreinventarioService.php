<?php

namespace App\Services;

use App\Models\Preinventario;
use Illuminate\Support\Facades\Validator;

class PreinventarioService
{
    public function validarPreinventario(array $request)
    {
        $rules = [
            'nombre' => 'required',
        ];

        $messages = [
            'nombre.required' => 'El campo Nombre es obligatorio.',
        ];

        $validator = Validator::make($request, $rules, $messages);

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

    public function crearPreinventario(array $request)
    {
        $validacion = $this->validarPreinventario($request);
        if (!$validacion['status']) return $validacion;

        try {
            $comida = Preinventario::create($request);
            return [
                'status' => true,
                'message' => 'Preinventario creado correctamente',
                'data' => $comida
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al crear el preinventario',
                'error' => $e->getMessage()
            ];
        }
    }

    public function obtenerPreinventarios()
    {
        return Preinventario::with(['items.itemable'])->get();
    }

    public function actualizarPreinventario($id, $request)
    {
        $validate = $this->validarPreinventario($request);
        if (!$validate['status']) return $validate;

        try {
            $preinventario = Preinventario::findOrFail($id);
            $preinventario->update($request);
            return [
                'status' => true,
                'message' => 'Preinventario actualizado correctamente',
                'data' => $preinventario
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al actualizar el preinventario',
                'error' => $e->getMessage()
            ];
        }
    }

    public function eliminarPreinventario($id)
    {
        try {
            $comida = Preinventario::findOrFail($id);
            $comida->delete();
            return [
                'status' => true,
                'message' => 'Preinventario eliminado correctamente'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al eliminar el preinventario',
                'error' => $e->getMessage()
            ];
        }
    }
}
