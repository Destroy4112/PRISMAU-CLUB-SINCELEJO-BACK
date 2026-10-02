<?php

namespace App\Http\Controllers;

use App\Services\CocinaService;
use Illuminate\Http\Request;

class CocinaController extends Controller
{

    protected $service;

    public function __construct(CocinaService $restauranteService)
    {
        $this->service = $restauranteService;
    }

    public function crearCocina(Request $request)
    {
        $response = $this->service->crearCocina($request->all());
        return response()->json($response, $response['code'] ?? 200);
    }

    public function obtenerCocinas()
    {
        $response = $this->service->obtenerCocinas();
        return response()->json($response, $response['code'] ?? 200);
    }

    public function actualizarCocina(Request $request, $id)
    {
        $response = $this->service->actualizarCocina($request->all(), $id);
        return response()->json($response, $response['code'] ?? 200);
    }

    public function asignCocinero(Request $request, $id)
    {
        $response = $this->service->asignCocinero($request->all(), $id);
        return response()->json($response, $response['code'] ?? 200);
    }

    public function eliminarCocina($id)
    {
        $response = $this->service->eliminarCocina($id);
        return response()->json($response, $response['code'] ?? 200);
    }
}
