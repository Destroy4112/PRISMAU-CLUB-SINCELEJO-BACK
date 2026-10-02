<?php

namespace App\Http\Controllers;

use App\Services\ProductoService;
use Illuminate\Http\Request;

class ProductoController extends Controller
{

    public function __construct(private readonly ProductoService $service) {}

    public function create(Request $request)
    {
        $resp = $this->service->crearProducto($request);
        $code = $resp['code'] ??  200;
        return response()->json($resp, $code);
    }

    public function list()
    {
        $resp = $this->service->obtenerProductos();
        $code = $resp['code'] ??  200;
        return response()->json($resp, $code);
    }

    public function listDisponibles()
    {
        $resp = $this->service->obtenerProductosDisponibles();
        $code = $resp['code'] ??  200;
        return response()->json($resp, $code);
    }

    public function update(Request $request, $id)
    {
        $resp = $this->service->actualizarProducto($request, $id);
        $code = $resp['code'] ??  200;
        return response()->json($resp, $code);
    }

    public function delete($id)
    {
        $resp = $this->service->eliminarProducto($id);
        $code = $resp['code'] ??  200;
        return response()->json($resp, $code);
    }
}
