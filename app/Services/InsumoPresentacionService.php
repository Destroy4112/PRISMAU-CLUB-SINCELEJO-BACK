<?php

namespace App\Services;

use App\Models\InsumoPresentacion;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class InsumoPresentacionService
{

    public function validarInsumoPresentacion(array $request, $id = null)
    {
        $validator = Validator::make($request, [
            'insumo_id' => ['required', 'exists:insumos,id'],
            'nombre' => [
                'required',
                'string',
                Rule::unique('insumo_presentaciones', 'nombre')
                    ->where(fn($q) => $q->where('insumo_id', $request['insumo_id']))
                    ->ignore($id),
            ],
            'stock' => ['required', 'integer', 'min:1'],
        ], [
            'insumo_id.required' => 'El campo insumo es obligatorio.',
            'insumo_id.exists' => 'El insumo no existe en el sistema.',
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.unique' => 'Esta presentación ya está registrada para este insumo.',
            'stock.required' => 'El campo stock es requerido',
            'stock.min' => 'El stock debe ser mayor a 0.',
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

    public function crearInsumoPresentacion(array $request)
    {
        $validacion = $this->validarInsumoPresentacion($request);
        if (!$validacion['status']) return $validacion;

        try {
            $insumo = InsumoPresentacion::create($request);

            return [
                'status' => true,
                'message' => 'Presentación creada correctamente',
                'data' => $insumo
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al crear la presentación',
                'error' => $e->getMessage()
            ];
        }
    }

    public function obtenerInsumos()
    {
        $insumos = InsumoPresentacion::with('insumo')->get();
        $insumos->transform(function ($insumo) {
            return [
                'id' => $insumo->id,
                'nombre' => $insumo->insumo->nombre . ' - ' . $insumo->nombre,
            ];
        });

        return $insumos;
    }

    public function obtenerInsumosPresentacion($id)
    {
        return InsumoPresentacion::where('insumo_id', $id)->with('insumo')->get();
    }

    public function actualizarInsumoPresentacion($id, array  $request)
    {
        $validate = $this->validarInsumoPresentacion($request, $id);
        if (!$validate['status']) return $validate;

        try {
            $insumo = InsumoPresentacion::findOrFail($id);
            $insumo->update($request);
            return [
                'status' => true,
                'message' => 'Presentación actualizada correctamente',
                'data' => $insumo
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al actualizar la presentación',
                'error' => $e->getMessage()
            ];
        }
    }

    public function eliminarInsumoPresentacion($id)
    {
        try {
            $insumo = InsumoPresentacion::findOrFail($id);
            $insumo->delete();
            return [
                'status' => true,
                'message' => 'Presentación eliminada correctamente'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al eliminar la presentación',
                'error' => $e->getMessage()
            ];
        }
    }

    public function descontarPorVenta($presentacionId, $cantidad)
    {
        try {
            $cantidad = (int) $cantidad;
            if ($cantidad <= 0) {
                return ['status' => false, 'errors' => ['Cantidad inválida.'],];
            }

            $presentacion = InsumoPresentacion::where('id', $presentacionId)->lockForUpdate()->first();

            if (!$presentacion) {
                return ['status' => false, 'errors' => ["No existe la presentación solicitada."],];
            }

            if ((int)$presentacion->stock < $cantidad) {
                return [
                    'status' => false,
                    'errors' => ["Stock insuficiente. Disponible: {$presentacion->stock}."],
                ];
            }

            $presentacion->stock -= $cantidad;
            $presentacion->save();
            return ['status' => true];

        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al descontar por venta.',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function descontarAlActualizar($presentacionId, $delta)
    {
        try {
            $delta = (int) $delta;

            if ($delta === 0) {
                return ['status' => true];
            }

            $insumo = InsumoPresentacion::where('id', $presentacionId)->lockForUpdate()->first();

            if (!$insumo) {
                return [
                    'status' => false,
                    'errors' => ["El insumo solicitado no existe."],
                ];
            }

            if ($delta > 0) {
                if ((int)$insumo->stock < $delta) {
                    return [
                        'status' => false,
                        'errors' => ["Stock insuficiente. Disponible: {$insumo->stock}."],
                    ];
                }

                $insumo->stock -= $delta;
                $insumo->save();

                return ['status' => true];
            }

            $cantidadADevolver = abs($delta);

            $insumo->stock += $cantidadADevolver;
            $insumo->save();

            return ['status' => true];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al ajustar inventario por edición (por itemable).',
                'error' => $e->getMessage(),
            ];
        }
    }
}
