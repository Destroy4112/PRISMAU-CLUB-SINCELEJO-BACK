<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contrato\CreateContratoRequest;
use App\Services\ContratosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ContratosController extends Controller
{

   public function __construct(protected ContratosService $service) {}

   public function create(CreateContratoRequest $request): JsonResponse
   {
      try {
         $this->service->create($request->validated());
         return response()->json([
            'status' => true,
            'message' => 'Registrado correctamente',
         ], 200);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function get(Request $request): JsonResponse
   {
      try {
         $paginator = $this->service->get($request->all());
         return response()->json([
            'data'  => $paginator->items(),
            'total' => $paginator->total(),
            'page'  => $paginator->currentPage(),
            'limit' => $paginator->perPage(),
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
