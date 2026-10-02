<?php

namespace App\Services;

use App\Models\Inventario;
use App\Models\InventarioItem;
use Carbon\Carbon;

class InventarioItemService
{
    public function descontarPorVenta($inventarioItemId, $cantidad)
    {
        try {
            $cantidad = (int) $cantidad;
            if ($cantidad <= 0) {
                return [
                    'status' => false,
                    'errors' => ['Cantidad inválida.'],
                ];
            }

            $fecha = Carbon::now('America/Bogota')->toDateString();

            $inventario = Inventario::where('fecha', $fecha)
                ->where('estado', 'ABIERTO')
                ->lockForUpdate()
                ->first();

            if (!$inventario) {
                return [
                    'status' => false,
                    'errors' => ["No existe inventario ABIERTO para la fecha {$fecha}."],
                ];
            }

            $itemInv = InventarioItem::where('id', $inventarioItemId)
                ->where('inventario_id', $inventario->id)
                ->lockForUpdate()
                ->first();

            if (!$itemInv) {
                return [
                    'status' => false,
                    'errors' => ["El producto no está en el inventario del día."],
                ];
            }

            if ((int)$itemInv->cantidad_disponible < $cantidad) {
                return [
                    'status' => false,
                    'errors' => ["Stock insuficiente. Disponible: {$itemInv->cantidad_disponible}."],
                ];
            }

            $itemInv->cantidad_disponible -= $cantidad;

            $itemInv->cantidad_vendida += $cantidad;

            $itemInv->save();

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

    public function descontarAlActualizar($inventario_id, $itemableType, $itemableId, $delta)
    {
        try {
            $delta = (int) $delta;

            if ($delta === 0) {
                return ['status' => true];
            }


            $inventario = Inventario::where('id', $inventario_id)
                ->where('estado', 'ABIERTO')
                ->lockForUpdate()
                ->first();

            if (!$inventario) {
                return [
                    'status' => false,
                    'errors' => ["No existe inventario ABIERTO para la fecha {$inventario->fecha}."],
                ];
            }

            $itemInv = InventarioItem::where('inventario_id', $inventario->id)
                ->where('itemable_type', $itemableType)
                ->where('itemable_id', (int) $itemableId)
                ->lockForUpdate()
                ->first();

            if (!$itemInv) {
                return [
                    'status' => false,
                    'errors' => ["El producto no está en el inventario del día."],
                ];
            }

            if ($delta > 0) {
                if ((int)$itemInv->cantidad_disponible < $delta) {
                    return [
                        'status' => false,
                        'errors' => ["Stock insuficiente. Disponible: {$itemInv->cantidad_disponible}."],
                    ];
                }

                $itemInv->cantidad_disponible -= $delta;
                $itemInv->cantidad_vendida += $delta;
                $itemInv->save();

                return ['status' => true];
            }

            $cantidadADevolver = abs($delta);

            if ((int)$itemInv->cantidad_vendida < $cantidadADevolver) {
                return [
                    'status' => false,
                    'errors' => ["No se puede devolver más de lo vendido. Vendida: {$itemInv->cantidad_vendida}."],
                ];
            }

            $itemInv->cantidad_disponible += $cantidadADevolver;
            $itemInv->cantidad_vendida -= $cantidadADevolver;
            $itemInv->save();

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
