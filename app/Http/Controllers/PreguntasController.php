<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pregunta\CreatePreguntaRequest;
use App\Http\Requests\Pregunta\UpdatePreguntaRequest;
use App\Services\PreguntasService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Throwable;

class PreguntasController extends Controller
{

   public function __construct(protected PreguntasService $service) {}

   public function create(CreatePreguntaRequest $request): JsonResponse
   {
      try {
         $this->service->create($request->validated());
         return response()->json([
            'status' => true,
            'message' => 'Pregunta creada exitosamente',
         ]);
      } catch (Throwable $th) {
         report($th);
         return response()->json([
            'status' => false,
            'message' => $th->getMessage(),
         ], 500);
      }
   }

   public function getByEncuesta(int $id): JsonResponse
   {
      try {
         $response = $this->service->getByEncuesta($id);
         return response()->json($response);
      } catch (Throwable $th) {
         report($th);
         return response()->json([
            'status' => false,
            'message' => $th->getMessage(),
         ], 500);
      }
   }

   public function update(UpdatePreguntaRequest $request, int $id): JsonResponse
   {
      try {
         $this->service->update($request->validated(), $id);
         return response()->json([
            'status' => true,
            'message' => 'Pregunta actualizada correctamente',
         ]);
      } catch (ModelNotFoundException $th) {
         report($th);
         return response()->json([
            'status' => false,
            'message' => 'Pregunta no encontrada',
         ], 404);
      } catch (Throwable $th) {
         return response()->json([
            'status' => false,
            'message' => $th->getMessage(),
         ], 500);
      }
   }

   public function delete(int $id): JsonResponse
   {
      try {
         $this->service->delete($id);
         return response()->json([
            'status' => true,
            'message' => 'Pregunta eliminada correctamente',
         ]);
      } catch (ModelNotFoundException $th) {
         report($th);
         return response()->json([
            'status' => false,
            'message' => 'Pregunta no encontrada',
         ], 404);
      } catch (Throwable $th) {
         return response()->json([
            'status' => false,
            'message' => $th->getMessage(),
         ], 500);
      }
   }
}
