<?php

namespace App\Http\Controllers;

use App\Services\PedidoService;
use Illuminate\Http\Request;

class PedidoController extends Controller
{

    public function __construct(private readonly PedidoService $service) {}

    public function crearPedido(Request $request)
    {
        $response = $this->service->crearPedido($request);
        return response()->json($response, $response['code'] ?? 200);
    }

    public function addItemsPedido(Request $request)
    {
        $response = $this->service->addItemsPedido($request);
        return response()->json($response, $response['code'] ?? 200);
    }

    public function obtenerPedidosAbiertos()
    {
        $response = $this->service->obtenerPedidosAbiertos();
        return response()->json($response);
    }

    public function getPedidosCerrados()
    {
        $response = $this->service->getPedidosCerrados();
        return response()->json($response);
    }

    public function getPedidosCocina($id)
    {
        $response = $this->service->getPedidosCocina($id);
        return response()->json($response);
    }

    public function getPedidoMesa($id)
    {
        $response = $this->service->getPedidoMesa($id);
        return response()->json($response);
    }

    public function cambiarEstadoPedido($id, $estado)
    {
        $response = $this->service->cambiarEstadoPedido($id, $estado);
        return response()->json($response, $response['code'] ?? 200);
    }

    public function cambiarMesaPedido($id, Request $request)
    {
        $response = $this->service->cambiarMesaPedido($id, $request->all());
        return response()->json($response, $response['code'] ?? 200);
    }

    public function cancelarPedido($id)
    {
        $response = $this->service->cancelarPedido($id);
        return response()->json($response, $response['code'] ?? 200);
    }
}
