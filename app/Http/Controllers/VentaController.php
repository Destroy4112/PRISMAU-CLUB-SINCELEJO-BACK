<?php

namespace App\Http\Controllers;

use App\Services\VentaService;
use Illuminate\Http\Request;

class VentaController extends Controller
{

    public function __construct(private readonly VentaService $service) {}

    public function create(Request $request)
    {
        $resp = $this->service->crearVenta($request->all());
        $code = $resp['code'] ??  200;

        return response()->json($resp, $code);
    }
}
