<?php

namespace App\Http\Controllers;

use App\Services\MesaService;
use Illuminate\Http\Request;

class MesaController extends Controller
{

    protected $service;

    public function __construct(MesaService $service)
    {
        $this->service = $service;
    }

    public function create(Request $request)
    {
        $mesa = $this->service->crearMesa($request);
        return response()->json($mesa, $mesa['code'] ?? 200);
    }

    public function getAll($id)
    {
        $mesas = $this->service->obtenerMesas($id);
        return response()->json($mesas, $mesas['code'] ?? 200);
    }

    public function update(Request $request, $id)
    {
        $mesa = $this->service->actualizarMesa($request, $id);
        return response()->json($mesa, $mesa['code'] ?? 200);
    }

    public function delete($id)
    {
        $mesa = $this->service->eliminarMesa($id);
        return response()->json($mesa, $mesa['code'] ?? 200);
    }
}
