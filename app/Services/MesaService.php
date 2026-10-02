<?php

namespace App\Services;

use App\Models\Mesa;
use Illuminate\Support\Facades\Validator;

class MesaService
{
    public function validarMesa($request)
    {
        $rules = [
            'numero' => 'required|string',
            'ubicacion_id' => 'required|exists:ubicaciones,id',
        ];

        $messages = [
            'numero.required' => 'El campo Numero es obligatorio.',
            'ubicacion_id.required' => 'El campo Ubicacion es obligatorio.',
            'ubicacion_id.exists' => 'La Ubicacion seleccionada no es válida.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

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

    public function crearMesa($request)
    {
        $validacion = $this->validarMesa($request);
        if (!$validacion['status']) return $validacion;
        try {
            $mesa = Mesa::create($request->all());
            return [
                'status' => true,
                'message' => 'Mesa creada correctamente',
                'data' => $mesa
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al crear la mesa',
                'error' => $e->getMessage()
            ];
        }
    }

    public function obtenerMesas($id)
    {
        return Mesa::where('ubicacion_id', $id)->get();
    }
    
    public function actualizarMesa($request, $id)
    {
        $validate = $this->validarMesa($request);
        if (!$validate['status']) return $validate;
        try {
            $mesa = Mesa::findOrFail($id);
            $mesa->update($request->all());
            return [
                'status' => true,
                'message' => 'Mesa actualizada correctamente',
                'data' => $mesa
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al actualizar la mesa',
                'error' => $e->getMessage()
            ];
        }
    }

    public function cambiarEstadoMesa($id, $estado)
    {
        try {
            $mesa = Mesa::findOrFail($id);
            $mesa->estado = $estado;
            $mesa->save();
            return [
                'status' => true,
                'message' => 'Estado de la mesa actualizado correctamente',
                'data' => $mesa
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al actualizar el estado de la mesa',
                'error' => $e->getMessage()
            ];
        }
    }

    public function eliminarMesa($id)
    {
        try {
            $mesa = Mesa::findOrFail($id);
            $mesa->delete();
            return [
                'status' => true,
                'message' => 'Mesa eliminada correctamente'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al eliminar la mesa',
                'error' => $e->getMessage()
            ];
        }
    }
}
