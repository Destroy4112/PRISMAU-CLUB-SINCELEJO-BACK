<?php

namespace App\Services;

use App\Models\Pedido;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PedidoService
{

    protected $pedidoDetalle;
    protected $mesa;
    protected $ventaService;

    public function __construct(PedidoDetalleService $pedidoDetalle, MesaService $mesa, VentaService $ventaService)
    {
        $this->pedidoDetalle = $pedidoDetalle;
        $this->mesa = $mesa;
        $this->ventaService = $ventaService;
    }

    public function validarPedido($request)
    {
        $rules = [
            'user_id' => 'required|exists:users,id',
            'mesa_id' => 'required|exists:mesas,id',
            'total' => 'required',
            'pedido_detalle' => 'required|array',
        ];

        $messages = [
            'user_id.required' => 'El campo user_id es requerido.',
            'user_id.exists' => 'El usuario no existe.',
            'mesa_id.required' => 'El campo mesa_id es requerido.',
            'mesa_id.exists' => 'La mesa no existe.',
            'pedido_detalle.required' => 'El campo detalle es requerido.',
            'pedido_detalle.array' => 'El campo detalle debe ser un arreglo.',
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

    public function crearPedido($request)
    {
        $validacion = $this->validarPedido($request);
        if (!$validacion['status']) return $validacion;

        DB::beginTransaction();
        try {

            $pedido = Pedido::create([
                'user_id' => $request->user_id,
                'mesa_id' => $request->mesa_id,
                'estado' => 'Abierto',
                'total' => 0
            ]);

            foreach ($request->pedido_detalle as $detalle) {
                $resp = $this->pedidoDetalle->crearPedidoDetalle($detalle, $pedido->id);
                if (is_array($resp) && ($resp['status'] ?? false) === false) {
                    DB::rollBack();
                    return $resp;
                }
            }

            $pedido->recalcularTotal();

            $this->mesa->cambiarEstadoMesa($request->mesa_id, 'ocupada');

            DB::commit();

            return [
                'status' => true,
                'message' => 'Pedido creado correctamente',
                'data' => $pedido
            ];
        } catch (\Exception $e) {

            DB::rollBack();

            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al crear el pedido',
                'error' => $e->getMessage()
            ];
        }
    }

    public function addItemsPedido($request)
    {
        try {
            return DB::transaction(function () use ($request) {

                $pedidoId = (int) data_get($request, 'id');
                $detalles = data_get($request, 'pedido_detalle', []);

                if (!is_array($detalles) || count($detalles) === 0) {
                    return [
                        'status' => false,
                        'errors' => ['Debe enviar al menos un item en pedido_detalle.',]
                    ];
                }

                $pedido = Pedido::lockForUpdate()->findOrFail($pedidoId);

                if (in_array($pedido->estado, ['Pagado', 'Rechazado', 'Cancelado'], true)) {
                    return [
                        'status' => false,
                        'errors' => ['No se puede agregar productos: el pedido ya está cerrado.',]
                    ];
                }

                foreach ($detalles as $detalle) {
                    $resp = $this->pedidoDetalle->crearPedidoDetalle($detalle, $pedido->id);
                    if (is_array($resp) && ($resp['status'] ?? false) === false) {
                        return $resp;
                    }
                }

                $pedido->recalcularTotal();

                return [
                    'status' => true,
                    'message' => 'Pedido creado correctamente',
                    'data' => $pedido
                ];
            });
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al crear el pedido',
                'error' => $e->getMessage()
            ];
        }
    }

    public function obtenerPedidosAbiertos()
    {
        $pedidos = Pedido::with('mesa', 'mesa.ubicacion', 'pedidoDetalle')
            ->whereIn('estado', ['Abierto', 'En Preparacion', 'Preparado', 'Servido'])
            ->orderByRaw("estado = 'Preparado'")
            ->orderBy('created_at', 'asc')
            ->get();


        if ($pedidos->isEmpty()) return [];

        $pedidos->transform(function ($pedido) {
            $user = $pedido->user;
            if ($user) {
                if ($user->Rol == 2 && $user->asociado) {
                    $pedido->usuario = $user->asociado->withoutRelations();
                } elseif ($user->Rol == 3 && $user->adherente) {
                    $pedido->usuario = $user->adherente->withoutRelations();
                } else {
                    $pedido->usuario = null;
                }
            } else {
                $pedido->usuario = null;
            }
            unset($pedido->user);
            return $pedido;
        });

        return $pedidos;
    }

    public function getPedidosCerrados()
    {
        $pedidos = Pedido::with('mesa', 'mesa.ubicacion', 'pedidoDetalle.producto', 'user')
            ->whereIn('estado', ['Pagado', 'Cancelado', 'Rechazado'])
            ->orderBy('created_at', 'asc')->get();

        if ($pedidos->isEmpty()) {
            return [];
        }

        $pedidos->transform(function ($pedido) {
            $user = $pedido->user;
            if ($user) {
                if ($user->Rol == 2 && $user->asociado) {
                    $pedido->usuario = $user->asociado->withoutRelations();
                } elseif ($user->Rol == 3 && $user->adherente) {
                    $pedido->usuario = $user->adherente->withoutRelations();
                } else {
                    $pedido->usuario = null;
                }
            } else {
                $pedido->usuario = null;
            }
            unset($pedido->user);
            return $pedido;
        });

        return $pedidos;
    }

    public function getPedidosCocina($cocinaId)
    {
        return $this->pedidoDetalle->getPedidosCocina($cocinaId);
    }

    public function getPedidoMesa($id)
    {
        $pedido = Pedido::where('mesa_id', $id)
            ->whereIn('estado', ['Abierto', 'En Preparacion', 'Preparado', 'Servido'])
            ->with(['pedidoDetalle.producto', 'mesa.ubicacion', 'user.asociado', 'user.adherente',])
            ->first();

        if (!$pedido) return null;

        $user = $pedido->user;

        if ($user) {
            if ($user->Rol == 2 && $user->asociado) {
                $pedido->usuario = $user->asociado->withoutRelations();
            } elseif ($user->Rol == 3 && $user->adherente) {
                $pedido->usuario = $user->adherente->withoutRelations();
            } else {
                $pedido->usuario = null;
            }
        } else {
            $pedido->usuario = null;
        }

        unset($pedido->user);

        return $pedido;
    }

    public function cambiarEstadoPedido($id, $estado)
    {
        try {
            $pedido = Pedido::findOrFail($id);

            $pedido->estado = $estado;
            $pedido->save();

            return [
                'status' => true,
                'message' => 'Pedido actualizado correctamente',
                'data' => $pedido
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al actualizar el estado del pedido',
                'error' => $e->getMessage()
            ];
        }
    }

    public function cambiarMesaPedido($id, array $data)
    {
        DB::beginTransaction();
        try {
            $pedido = Pedido::findOrFail($id);

            $this->mesa->cambiarEstadoMesa($pedido->mesa_id, 'libre');

            $pedido->mesa_id = $data['mesa_id'];
            $pedido->save();

            $this->mesa->cambiarEstadoMesa($data['mesa_id'], 'ocupada');

            DB::commit();
            return [
                'status' => true,
                'message' => 'Pedido actualizado correctamente',
                'data' => $pedido
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al actualizar el estado del pedido',
                'error' => $e->getMessage()
            ];
        }
    }

    public function cancelarPedido($id)
    {
        try {
            return DB::transaction(function () use ($id) {

                $pedido = Pedido::lockForUpdate()->findOrFail($id);

                if (in_array($pedido->estado, ['Pagado', 'Rechazado', 'Cancelado'], true)) {
                    throw new \RuntimeException(json_encode([
                        'status' => false,
                        'code' => 200,
                        'errors' => ['El pedido ya ha sido ' . $pedido->estado],
                    ]));
                }
                foreach ($pedido->pedidoDetalle as $detalle) {
                    $this->pedidoDetalle->cancelarPedidoDetalle((int) $detalle->id);
                }

                $this->mesa->cambiarEstadoMesa($pedido->mesa_id, 'libre');

                $pedido->update(['estado' => 'Cancelado']);

                return [
                    'status' => true,
                    'message' => 'Pedido cancelado correctamente',
                    'data' => $pedido
                ];
            });
        } catch (\RuntimeException $e) {
            $payload = json_decode($e->getMessage(), true);
            if (is_array($payload) && isset($payload['status'])) {
                $payload['code'] = 200;
                return $payload;
            }
            return [
                'status' => false,
                'code' => 200,
                'message' => 'Error controlado al cancelar el pedido',
                'errors' => [$e->getMessage()],
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'code' => 500,
                'message' => 'Error al cancelar el pedido',
                'error' => $e->getMessage()
            ];
        }
    }
}
