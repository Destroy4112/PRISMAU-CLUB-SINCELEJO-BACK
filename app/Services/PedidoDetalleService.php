<?php

namespace App\Services;

use App\Models\PedidoDetalle;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PedidoDetalleService
{

    public function __construct(private readonly InsumoPresentacionService $insumoService) {}

    public function validarPedidoDetalle(array $request)
    {
        $rules = [
            'insumo_presentacion_id' => 'nullable|exists:insumo_presentaciones,id',
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
            'observaciones' => 'nullable|string',
            'estado' => 'nullable|in:Pendiente,En Preparacion,Servido,Rechazado',
        ];

        $messages = [
            'insumo_presentacion_id.exists' => 'El insumo no existe en el sistema.',
            'producto_id.required' => 'El campo producto es requerido.',
            'producto_id.exists' => 'El producto no existe en el sistema.',
            'cantidad.required' => 'El campo cantidad es requerido.',
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

    private function obtenerPrecioUnitario(int $id): int
    {
        return Producto::findOrFail($id)->precio;
    }

    public function crearPedidoDetalle($detalle, $pedidoId)
    {
        $validator = $this->validarPedidoDetalle($detalle);
        if (!$validator['status']) return $validator;
        try {
            return DB::transaction(function () use ($detalle, $pedidoId) {

                $itemId = (int) $detalle['producto_id'];
                $cantidad = (int) $detalle['cantidad'];
                $tipo =  $detalle['producto']['tipo'];

                $precioUnitario = $this->obtenerPrecioUnitario($itemId);
                $subtotal = $precioUnitario * $cantidad;

                if ($tipo === "COMIDA") {
                    $res = $this->insumoService->descontarPorVenta($detalle['insumo_presentacion_id'], $cantidad);
                    if (!$res['status']) return $res;
                }

                return PedidoDetalle::create([
                    'pedido_id' => $pedidoId,
                    'producto_id' => $itemId,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $subtotal,
                    'observaciones' => $detalle['observaciones'] ?? null,
                    'estado' => $detalle['estado'] ?? 'Pendiente',
                ]);
            });
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al crear el detalle del pedido',
                'error' => $e->getMessage()
            ];
        }
    }

    public function getPedidosCocina($cocinaId)
    {
        $detalles = PedidoDetalle::query()
            ->whereIn('estado', ['Pendiente', 'En Preparacion'])
            ->whereHas('pedido', function ($q) {
                $q->whereIn('estado', ['Abierto', 'En Preparacion']);
            })
            ->whereHas('producto', function ($q) use ($cocinaId) {
                $q->where('tipo', 'COMIDA')
                    ->where('cocina_id', $cocinaId);
            })
            ->with(['producto', 'pedido.mesa.ubicacion',])
            ->orderBy('created_at', 'asc')
            ->get();

        if ($detalles->isEmpty()) return [];

        $cola = $detalles->groupBy('pedido_id')->values()->map(function ($items) {
            $first = $items->first();
            $pedido = $first->pedido;

            return [
                'pedido_id' => $pedido->id,
                'inventario_id' => $pedido->inventario_id,
                'user_id' => $pedido->user_id,
                'mesa_id' => $pedido->mesa_id,
                'estado' => $pedido->estado,
                'total' => (int) $pedido->total,
                'created_at' => $pedido->created_at,
                'updated_at' => $pedido->updated_at,
                'mesa' => $pedido->mesa,
                'inicio_bloque' => $first->created_at,
                'pedido_detalle' => $items->values(),
            ];
        })->toArray();

        return $cola;
    }

    public function cambiarEstadoPedidoDetalle($id, $estado)
    {
        try {
            PedidoDetalle::where('id', $id)->update(['estado' => $estado]);
            return [
                'status' => true,
                'message' => 'Estado actualizado correctamente',
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al actualizar el detalle del pedido',
                'error' => $e->getMessage()
            ];
        }
    }

    public function editarPedidoDetalle($id, $data)
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                $detalle = PedidoDetalle::lockForUpdate()->findOrFail($id);

                $cantidadAnterior = (int) $detalle->cantidad;
                $cantidadNueva = (int) ($data['cantidad'] ?? $cantidadAnterior);

                if ($cantidadNueva <= 0) {
                    return ['status' => false, 'errors' => ['Cantidad inválida.'],];
                }

                if ($cantidadNueva === $cantidadAnterior) {
                    $detalle->update(['observaciones' => $data['observaciones'] ?? $detalle->observaciones,]);

                    return [
                        'status' => true,
                        'message' => 'Detalle del pedido actualizado correctamente',
                    ];
                }

                $itemId = (int) $detalle->producto_id;

                $precioUnitario = $this->obtenerPrecioUnitario($itemId);
                $subtotal = $precioUnitario * $cantidadNueva;

                $delta = $cantidadNueva - $cantidadAnterior;

                $res = $this->insumoService->descontarAlActualizar((int) $data['producto']['insumo_presentacion_id'], $delta);

                if (!$res['status']) return $res;

                $detalle->update([
                    'cantidad' => $cantidadNueva,
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $subtotal,
                    'observaciones' => $data['observaciones'] ?? $detalle->observaciones,
                    'estado' => $data['estado'] ?? $detalle->estado,
                ]);

                return [
                    'status' => true,
                    'message' => 'Detalle del pedido actualizado correctamente',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al actualizar el detalle del pedido',
                'error' => $e->getMessage()
            ];
        }
    }

    public function eliminarPedidoDetalle($id, $presentacionId)
    {
        try {
            return DB::transaction(function () use ($id, $presentacionId) {

                $detalle = PedidoDetalle::lockForUpdate()->findOrFail($id);

                $cantidadAnterior = (int) $detalle->cantidad;

                if ($cantidadAnterior > 0) {

                    $delta = 0 - $cantidadAnterior;

                    $res = $this->insumoService->descontarAlActualizar((int) $presentacionId, $delta);

                    if (!$res['status']) return $res;
                }

                $detalle->delete();

                return [
                    'status' => true,
                    'message' => 'Detalle del pedido eliminado correctamente (stock devuelto).',
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al eliminar el detalle del pedido',
                'error' => $e->getMessage()
            ];
        }
    }

    public function cancelarPedidoDetalle($id)
    {
        $detalle = PedidoDetalle::with('producto')->lockForUpdate()->findOrFail($id);

        if (in_array($detalle->estado, ['Cancelado', 'Rechazado'], true)) {
            return;
        }

        if ($detalle->producto && strtoupper((string)$detalle->producto->tipo) === 'COMIDA') {

            $presentacionId = (int) ($detalle->producto->insumo_presentacion_id ?? 0);
            if ($presentacionId <= 0) {
                throw new \RuntimeException(json_encode([
                    'status' => false,
                    'code' => 200,
                    'message' => 'COMIDA sin insumo_presentacion_id. No se puede devolver stock.',
                    'errors' => [],
                ]));
            }

            $cantidad = (int) $detalle->cantidad;
            $delta = 0 - $cantidad;

            $res = $this->insumoService->descontarAlActualizar($presentacionId, $delta);

            if (($res['status'] ?? false) === false) {
                throw new \RuntimeException(json_encode($res + [
                    'status' => false,
                    'code' => 200,
                ]));
            }
        }

        $detalle->update(['estado' => 'Cancelado']);
    }
}
