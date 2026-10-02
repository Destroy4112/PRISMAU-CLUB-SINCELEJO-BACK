<?php

namespace App\Http\Controllers;

use App\Services\InsumoPresentacionService;
use Illuminate\Http\Request;

class InsumoPresentacionController extends Controller
{

    public function __construct(private readonly InsumoPresentacionService $service) {}

    public function create(Request $request)
    {
        $resp = $this->service->crearInsumoPresentacion($request->all());
        $code = $resp['code'] ??  200;
        return response()->json($resp, $code);
    }

    public function list($id)
    {
        $resp = $this->service->obtenerInsumosPresentacion($id);
        $code = $resp['code'] ??  200;
        return response()->json($resp, $code);
    }

    public function listAll()
    {
        $resp = $this->service->obtenerInsumos();
        $code = $resp['code'] ??  200;
        return response()->json($resp, $code);
    }

    public function update($id, Request $request)
    {
        $resp = $this->service->actualizarInsumoPresentacion($id, $request->all());
        $code = $resp['code'] ??  200;
        return response()->json($resp, $code);
    }

    public function delete($id)
    {
        $resp = $this->service->eliminarInsumoPresentacion($id);
        $code = $resp['code'] ??  200;
        return response()->json($resp, $code);
    }
}
