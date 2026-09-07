<?php

namespace App\Http\Controllers;

use App\Services\EstadoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class EstadosController extends Controller
{

   public function __construct(protected EstadoService $service) {}

   public function getAll(Request $request): JsonResponse
   {
      try {
         $response = $this->service->getAll($request->all());
         return response()->json($response);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }
}
