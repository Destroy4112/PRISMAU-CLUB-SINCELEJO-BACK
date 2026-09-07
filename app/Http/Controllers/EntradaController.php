<?php

namespace App\Http\Controllers;

use App\Services\EntradaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class EntradaController extends Controller
{

   public function __construct(protected EntradaService $service) {}

   public function create(int $id): JsonResponse
   {
      try {
         $this->service->crearEntrada($id);
         return response()->json([
            'status' => true,
            'message' => 'Entrada creada',
         ]);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function getAll(Request $request): JsonResponse
   {
      try {
         $response = $this->service->getAll($request->all());
         return response()->json([
            'data' => $response->items(),
            'total' => $response->total(),
            'page' => $response->currentPage(),
            'limit' => $response->perPage(),
         ]);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }
}
