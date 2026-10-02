<?php

namespace App\Http\Controllers;

use App\Services\InsumoService;
use Illuminate\Http\Request;

class InsumoController extends Controller
{

    public function __construct(private readonly InsumoService $service) {}

    public function create(Request $request)
    {
        $resp = $this->service->crearInsumo($request->all());
        $code = $resp['code'] ??  200;
        return response()->json($resp, $code);
    }

    public function list(Request $request)
    {
        $resp = $this->service->obtenerInsumos($request);
        $code = $resp['code'] ??  200;
        return response()->json($resp, $code);
    }

    public function update($id, Request $request)
    {
        $resp = $this->service->actualizarInsumo($id, $request->all());
        $code = $resp['code'] ??  200;
        return response()->json($resp, $code);
    }

    public function delete($id)
    {
        $resp = $this->service->eliminarInsumo($id);
        $code = $resp['code'] ??  200;
        return response()->json($resp, $code);
    }
}
