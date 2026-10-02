<?php

namespace App\Services;

use App\Models\Ubicacion;
use Illuminate\Support\Facades\Validator;

class UbicacionService
{
    public function validarUbicacion($request, $id = null)
    {
        $rules = [
            'ubicacion' => 'required|unique:ubicaciones,ubicacion' . ($id ? ',' . $id : ''),
        ];

        $messages = [
            'ubicacion.required' => 'El campo Ubicacion es obligatorio.',
            'ubicacion.unique' => 'La Ubicacion ya está registrada en el sistema.',
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

    public function crearUbicacion($request)
    {
        $validacion = $this->validarUbicacion($request);
        if (!$validacion['status']) return $validacion;
        try {
            $ubicacion = Ubicacion::create($request->all());
            return [
                'status' => true,
                'message' => 'Ubicacion creada correctamente',
                'data' => $ubicacion
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al crear la ubicacion',
                'error' => $e->getMessage()
            ];
        }
    }

    public function obtenerUbicacions()
    {
        return Ubicacion::all();
    }

    public function getUbicacionesWithMesas()
    {
        return Ubicacion::with([
            'mesas' => function ($query) {
                $query->with('pedidosActivos');
            }
        ])->get();
    }

    public function actualizarUbicacion($request, $id)
    {
        $validate = $this->validarUbicacion($request, $id);
        if (!$validate['status']) return $validate;
        try {
            $ubicacion = Ubicacion::findOrFail($id);
            $ubicacion->update($request->all());
            return [
                'status' => true,
                'message' => 'Ubicacion actualizada correctamente',
                'data' => $ubicacion
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al actualizar la ubicacion',
                'error' => $e->getMessage()
            ];
        }
    }

    public function eliminarUbicacion($id)
    {
        try {
            $ubicacion = Ubicacion::findOrFail($id);
            $ubicacion->delete();
            return [
                'status' => true,
                'message' => 'Ubicacion eliminada correctamente'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al eliminar la ubicacion',
                'error' => $e->getMessage()
            ];
        }
    }
}
