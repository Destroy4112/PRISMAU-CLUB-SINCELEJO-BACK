<?php

namespace App\Http\Controllers;

use App\Services\PedidoDetalleService;
use Illuminate\Http\Request;

class PedidoDetalleController extends Controller
{

    public function __construct(private readonly PedidoDetalleService $service) {}

    public function cambiarEstadoDetallePedido($id, $estado)
    {
        $res = $this->service->cambiarEstadoPedidoDetalle($id, $estado);
        return response()->json($res, $res['code'] ?? 200);
    }

    public function updateDetalle(Request $request, $id)
    {
        $res = $this->service->editarPedidoDetalle($id, $request->all());
        return response()->json($res, $res['code'] ?? 200);
    }

    public function deleteDetalle($id, $presentacionId)
    {
        $res = $this->service->eliminarPedidoDetalle($id, $presentacionId);
        return response()->json($res, $res['code'] ?? 200);
    }

    }
