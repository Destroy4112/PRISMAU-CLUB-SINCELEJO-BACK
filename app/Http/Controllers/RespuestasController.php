<?php

namespace App\Http\Controllers;

use App\Http\Requests\Respuesta\CreateRespuestaRequest;
use App\Http\Requests\Respuesta\UpdateRespuestaRequest;
use App\Services\RespuestasService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Throwable;

class RespuestasController extends Controller
{

   public function __construct(protected RespuestasService $service) {}

   public function create(CreateRespuestaRequest $request): JsonResponse
   {
      try {
         $this->service->create($request->validated());
         return response()->json([
            "status" => true,
            "message" => "Opción creada con exito",
         ], 201);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function getByPregunta(int $id): JsonResponse
   {
      try {
         $data = $this->service->getByPregunta($id);
         return response()->json($data, 200);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 200);
      }
   }

   public function update(UpdateRespuestaRequest $request, string $id): JsonResponse
   {
      try {
         $this->service->update($request->validated(), $id);
         return response()->json([
            "status" => true,
            "message" => "Opción actualizada con exito",
         ]);
      } catch (ModelNotFoundException $e) {
         report($e);
         return response()->json([
            "status" => false,
            "message" => "Opción no encontrada",
         ], 404);
      } catch (Throwable $e) {
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function delete(string $id): JsonResponse
   {
      try {
         $this->service->delete($id);
         return response()->json([
            "status" => true,
            "message" => "Opción eliminada con exito",
         ]);
      } catch (ModelNotFoundException $e) {
         report($e);
         return response()->json([
            "status" => false,
            "message" => "Opción no encontrada",
         ], 404);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }
}
