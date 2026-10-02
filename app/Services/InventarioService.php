<?php

namespace App\Services;

use App\Models\Inventario;
use App\Models\InventarioItem;
use App\Models\Preinventario;
use App\Models\PreinventarioItem;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InventarioService
{
    public function abrirInventario(int $preinventarioId, ?string $fecha = null)
    {
        $fecha = $fecha ?: now()->toDateString();

        try {
            $pre = Preinventario::findOrFail($preinventarioId);

            return DB::transaction(function () use ($pre, $fecha) {
                $inventario = Inventario::firstOrCreate(
                    ['fecha' => $fecha],
                    ['preinventario_id' => $pre->id, 'estado' => 'ABIERTO']
                );

                if ($inventario->estado === 'CERRADO') {
                    return [
                        'status' => false,
                        'code' => 409,
                        'message' => "El inventario del {$fecha} ya está CERRADO.",
                    ];
                }

                if ($inventario->items()->exists()) {
                    return [
                        'status' => true,
                        'message' => 'Inventario ya estaba creado para esta fecha.',
                        'data' => $inventario->load('items.itemable'),
                    ];
                }

                $preItems = PreinventarioItem::where('preinventario_id', $pre->id)
                    ->get(['itemable_type', 'itemable_id', 'cantidad_default']);

                $now = now();
                $rows = $preItems->map(function ($it) use ($inventario, $now) {
                    $cant = (int) $it->cantidad_default;
                    return [
                        'inventario_id' => $inventario->id,
                        'itemable_type' => $it->itemable_type,
                        'itemable_id' => (int) $it->itemable_id,
                        'cantidad_inicial' => $cant,
                        'cantidad_disponible' => $cant,
                        'cantidad_vendida' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                })->toArray();

                if (!empty($rows)) {
                    InventarioItem::insert($rows);
                }

                return [
                    'status' => true,
                    'message' => 'Inventario del día abierto y cargado desde el preinventario.',
                    'data' => $inventario->load('items.itemable'),
                ];
            });
        } catch (ModelNotFoundException $e) {
            return [
                'status' => false,
                'code' => 404,
                'message' => 'Preinventario no encontrado.',
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al abrir el inventario.',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function obtenerInventarios()
    {
        return Inventario::all();
    }

    public function obtenerInventarioPorFecha(?string $fecha = null)
    {
        $fecha = $fecha ?: now()->toDateString();

        $inv = Inventario::where('fecha', $fecha)
            ->with([
                'items' => function ($q) {
                    $q->where('cantidad_disponible', '>', 0)
                        ->with('itemable');
                }
            ])
            ->first();

        if (!$inv) {
            return [
                'status' => false,
                'message' => "No existe inventario para la fecha {$fecha}.",
            ];
        }

        return [
            'status' => true,
            'data' => $inv,
        ];
    }

    public function cerrarInventario(int $inventarioId)
    {
        try {
            $inv = Inventario::findOrFail($inventarioId);

            if ($inv->estado === 'CERRADO') {
                return [
                    'status' => true,
                    'message' => 'El inventario ya estaba cerrado.',
                    'data' => $inv,
                ];
            }

            $inv->estado = 'CERRADO';
            $inv->save();

            return [
                'status' => true,
                'message' => 'Inventario cerrado correctamente.',
                'data' => $inv,
            ];
        } catch (ModelNotFoundException $e) {
            return [
                'status' => false,
                'code' => 404,
                'message' => 'Inventario no encontrado.',
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al cerrar el inventario.',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function descontarPorVenta(?string $fecha, array $itemsVenta)
    {
        $fecha = $fecha ?: now()->toDateString();

        $validator = Validator::make(['items' => $itemsVenta], [
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:comida,bebida',
            'items.*.id' => 'required|integer|min:1',
            'items.*.cantidad' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return [
                'status' => false,
                'errors' => $validator->errors(),
            ];
        }

        try {
            return DB::transaction(function () use ($fecha, $itemsVenta) {
                $inv = Inventario::where('fecha', $fecha)->lockForUpdate()->firstOrFail();

                if ($inv->estado === 'CERRADO') {
                    return [
                        'status' => false,
                        'code' => 409,
                        'message' => 'El inventario está cerrado. No se puede descontar.',
                    ];
                }

                foreach ($itemsVenta as $it) {
                    $type = $it['type'];
                    $id = (int) $it['id'];
                    $cant = (int) $it['cantidad'];

                    $itemInv = InventarioItem::where('inventario_id', $inv->id)
                        ->where('itemable_type', $type)
                        ->where('itemable_id', $id)
                        ->lockForUpdate()
                        ->first();

                    if (!$itemInv) {
                        return [
                            'status' => false,
                            'code' => 404,
                            'message' => "El producto {$type}:{$id} no está en el inventario del día.",
                        ];
                    }

                    if ($itemInv->cantidad_disponible < $cant) {
                        return [
                            'status' => false,
                            'code' => 409,
                            'message' => "Stock insuficiente para {$type}:{$id}. Disponible: {$itemInv->cantidad_disponible}.",
                        ];
                    }

                    $itemInv->cantidad_disponible -= $cant;
                    $itemInv->cantidad_vendida += $cant;
                    $itemInv->save();
                }

                return [
                    'status' => true,
                    'message' => 'Inventario disponibleizado por venta.',
                    'data' => $inv->load('items.itemable'),
                ];
            });
        } catch (ModelNotFoundException $e) {
            return [
                'status' => false,
                'code' => 404,
                'message' => "No existe inventario para la fecha {$fecha}.",
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al descontar por venta.',
                'error' => $e->getMessage(),
            ];
        }
    }
}
