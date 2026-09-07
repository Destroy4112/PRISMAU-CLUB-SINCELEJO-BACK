<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reserva\CreateReservaRequest;
use App\Services\ReservasService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ReservasController extends Controller
{

   public function __construct(protected ReservasService $service) {}

   public function create(CreateReservaRequest $request): JsonResponse
   {
      try {
         $response = $this->service->create($request->validated());
         return response()->json($response);
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
         return response()->json($response);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function count(): JsonResponse
   {
      try {
         $response = $this->service->count();
         return response()->json($response);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function getByUser(int $id): JsonResponse
   {
      try {
         $response = $this->service->getByUser($id);
         return response()->json($response);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function countByUser(int $id): JsonResponse
   {
      try {
         $response = $this->service->countByUser($id);
         return response()->json($response);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function cancel(int $id): JsonResponse
   {
      try {
         $this->service->cancel($id);
         return response()->json([
            'status' => true,
            'message' => 'Reserva cancelada con exito',
         ]);
      } catch (ModelNotFoundException $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => 'Reserva no encontrada',
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
