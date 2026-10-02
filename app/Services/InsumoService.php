<?php

namespace App\Services;

use App\Models\Insumo;
use Illuminate\Support\Facades\Validator;

class InsumoService
{
    
    public function validarInsumo(array $request, $id = null)
    {

        $validator = Validator::make($request, [
            'nombre' => 'required|string|unique:insumos,nombre,' . $id,
            'unidad' => 'required|string',
        ], [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.unique' => 'El nombre ya esta registrado en el sistema.',
            'unidad.required' => 'El campo unidad es obligatorio.',
        ]);

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

    public function crearInsumo(array $request)
    {
        $validacion = $this->validarInsumo($request);
        if (!$validacion['status']) return $validacion;

        try {
            $insumo = Insumo::create($request);
            return [
                'status' => true,
                'message' => 'Insumo creado correctamente',
                'data' => $insumo
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al crear el insumo',
                'error' => $e->getMessage()
            ];
        }
    }

    public function obtenerInsumos($request)
    {
        $limit = (int) $request->input('limit', 30);
        $limit = max(1, min($limit, 100));

        $nombre = trim((string) $request->input('nombre', ''));

        $insumos = Insumo::query()
            ->when($nombre !== '', fn($q) => $q->where('nombre', 'like', "%{$nombre}%"))
            ->orderBy('nombre', 'asc');

        $paginator = $insumos->paginate($limit);

        return [
            'data'  => $paginator->items(),
            'total' => $paginator->total(),
            'page'  => $paginator->currentPage(),
            'limit' => $paginator->perPage(),
            'totalPages' => $paginator->lastPage(),
        ];
    }

    public function actualizarInsumo($id, array $request)
    {
        $validate = $this->validarInsumo($request, $id);
        if (!$validate['status']) return $validate;

        try {
            $insumo = Insumo::findOrFail($id);
            $insumo->update($request);
            return [
                'status' => true,
                'message' => 'Insumo actualizado correctamente',
                'data' => $insumo
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al actualizar la insumo',
                'error' => $e->getMessage()
            ];
        }
    }

    public function eliminarInsumo($id)
    {
        try {
            $insumo = Insumo::findOrFail($id);
            $insumo->delete();
            return [
                'status' => true,
                'message' => 'Insumo eliminado correctamente'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al eliminar la insumo',
                'error' => $e->getMessage()
            ];
        }
    }
}
