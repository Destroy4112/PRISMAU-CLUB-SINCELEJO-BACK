<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VentaService
{

    private $mesaService;
    private $pedidoDetalleService;

    public function __construct(MesaService $mesaService, PedidoDetalleService $pedidoDetalleService)
    {
        $this->mesaService = $mesaService;
        $this->pedidoDetalleService = $pedidoDetalleService;
    }

    private function validarVenta(array $request)
    {
        $validator = Validator::make($request, [
            'pedido_id' => 'required|exists:pedidos,id',
            'empleado_id' => 'nullable|exists:empleados,id',
            'metodo_pago' => 'string',
            'referencia_pago' => 'nullable|string',
            'observacion' => 'nullable|string',
        ], [
            'pedido_id.required' => 'Debe enviar pedido_id para cobrar el pedido.',
            'pedido_id.exists' => 'El pedido no existe.',
            'empleado_id.required' => 'Debe enviar empleado_id para cobrar el pedido.',
            'empleado_id.exists' => 'El empleado no existe.',
            'metodo_pago.string' => 'El metodo_pago debe ser una cadena de texto.',
            'referencia_pago.string' => 'La referencia_pago debe ser una cadena de texto.',
            'observacion.string' => 'La observacion debe ser una cadena de texto.',
        ]);

        if ($validator->fails()) {
            return ['status' => false, 'errors' => $validator->errors()->all()];
        }

        return ['status' => true];
    }

    public function crearVenta(array $request)
    {
        $val = $this->validarVenta($request);
        if (!$val['status']) return $val;

        try {
            return DB::transaction(function () use ($request) {

                $pedido = Pedido::with(['pedidoDetalle'])->lockForUpdate()->findOrFail((int)$request['pedido_id']);
    
                if (in_array($pedido->estado, ['Cerrado', 'Cancelado', 'Rechazado'], true)) {
                    return [
                        'status' => false,
                        'errors' => ['No se puede cobrar un pedido en estado: ' . $pedido->estado]
                    ];
                }

                $yaExiste = Venta::where('pedido_id', $pedido->id)->whereIn('estado', ['PAGADO'])->exists();
                if ($yaExiste) {
                    return [
                        'status' => false,
                        'errors' => ['Este pedido ya tiene una venta registrada.'],
                    ];
                }

                $detalle = $pedido->pedidoDetalle ?? collect();

                if ($detalle->count() === 0) {
                    return [
                        'status' => false,
                        'errors' => ['El pedido no tiene detalle para cobrar.'],
                    ];
                }

                $estadosFinales = ['Servido', 'Rechazado', 'Cancelado', 'cerrado'];

                $hayPendientes = $detalle->contains(function ($d) use ($estadosFinales) {
                    $estado = $d->estado ?? null;
                    return !in_array($estado, $estadosFinales, true);
                });

                if ($hayPendientes) {
                    return [
                        'status' => false,
                        'errors' => ['No se puede cobrar: hay ítems pendientes.'],
                    ];
                }

                $itemsCobrar = $detalle->filter(function ($d) {
                    return ($d->estado ?? null) === 'Servido';
                });

                if ($itemsCobrar->count() === 0) {
                    return [
                        'status' => false,
                        'errors' => ['No hay ítems en estado SERVIDO para cobrar.'],
                    ];
                }

                $subtotal = $this->calcularSubtotal($itemsCobrar);

                $descuento = $this->num($pedido->descuento ?? 0);
                $impuesto  = $this->num($pedido->impuesto ?? 0);

                $total = max(0, ($subtotal - $descuento) + $impuesto);

                $venta = Venta::create([
                    'pedido_id' => $pedido->id,
                    'empleado_id' => (int)$request['empleado_id'],
                    'subtotal' => $subtotal,
                    'descuento' => $descuento,
                    'impuesto' => $impuesto,
                    'total' => $total,
                    'metodo_pago' => $request['metodo_pago'],
                    'referencia_pago' => $request['referencia_pago'] ?? null,
                    'observacion' => $request['observacion'] ?? null,
                    'estado' => 'PAGADO',
                ]);

                $mesaRes = $this->mesaService->cambiarEstadoMesa($pedido->mesa_id, 'libre');
                if (!$mesaRes['status']) {
                    return $mesaRes;
                }

                foreach ($itemsCobrar as $d) {
                    $resDet = $this->pedidoDetalleService->cambiarEstadoPedidoDetalle($d->id, 'cerrado');
                    if (!$resDet['status']) {
                        return $resDet;
                    }
                }

                $pedido->total = $total;
                $pedido->estado = 'Pagado';
                $pedido->updated_at = now();
                $pedido->save();

                return [
                    'status' => true,
                    'message' => 'Venta creada y pedido cerrado correctamente.',
                    'data' => $venta,
                ];
            });
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error creando la venta.',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function calcularSubtotal($items): float
    {
        $subtotal = $items->reduce(function ($carry, $d) {
            if (isset($d->subtotal)) return $carry + $this->num($d->subtotal);

            $cantidad = $this->num($d->cantidad ?? 0);
            $precio   = $this->num($d->precio_unitario ?? 0);

            return $carry + ($cantidad * $precio);
        }, 0);

        return $this->num($subtotal);
    }

    private function num($value): float
    {
        return (float)(is_null($value) ? 0 : $value);
    }
}
