<?php

namespace App\Http\Controllers;

use App\Services\UbicacionService;
use Illuminate\Http\Request;

class UbicacionController extends Controller
{

    protected $service;

    public function __construct(UbicacionService $ubicacionService)
    {
        $this->service = $ubicacionService;
    }

    public function crearUbicacion(Request $request)
    {
        $response = $this->service->crearUbicacion($request);
        return response()->json($response, $response['code'] ?? 200);
    }

    public function obtenerUbicaciones()
    {
        $response = $this->service->obtenerUbicacions();
        return response()->json($response, $response['code'] ?? 200);
    }

    public function getUbicacionesWithMesas()
    {
        $response = $this->service->getUbicacionesWithMesas();
        return response()->json($response);
    }

    public function actualizarUbicacion(Request $request, $id)
    {
        $response = $this->service->actualizarUbicacion($request, $id);
        return response()->json($response, $response['code'] ?? 200);
    }

    public function eliminarUbicacion($id)
    {
        $response = $this->service->eliminarUbicacion($id);
        return response()->json($response, $response['code'] ?? 200);
    }
}
