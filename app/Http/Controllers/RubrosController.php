<?php

namespace App\Http\Controllers;

use App\Http\Requests\Rubro\CreateRubroRequest;
use App\Http\Requests\Rubro\UpdateRubroRequest;
use App\Models\Rubros;
use App\Services\RubrosService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class RubrosController extends Controller
{

   public function __construct(protected RubrosService $service) {}

   public function create(CreateRubroRequest $request): JsonResponse
   {
      try {
         $this->service->create($request->validated());
         return response()->json([
            'status' => true,
            'message' => 'Rubro creado exitosamente',
         ], 201);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'code' => 500,
            'message' => 'Error en el servidor: ' . $e->getMessage()
         ], 500);
      }
   }

   public function getAll(): JsonResponse
   {
      try {
         $response = $this->service->getAll();
         return response()->json($response);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage()
         ], 500);
      }
   }

   public function getPaginated(Request $request): JsonResponse
   {
      try {
         $response = $this->service->getPaginated($request->all());
         return response()->json($response);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage()
         ], 500);
      }
   }

   public function update(UpdateRubroRequest $request, Rubros $rubro): JsonResponse
   {
      try {
         $this->service->update($request->validated(), $rubro);
         return response()->json([
            'status' => true,
            'message' => 'Rubro actualizado correctamente.',
         ]);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage()
         ], 500);
      }
   }

   public function delete(int $id): JsonResponse
   {
      try {
         $this->service->delete($id);
         return response()->json([
            'status' => true,
            'message' => 'Rubro eliminado exitosamente',
         ], 200);
      } catch (ModelNotFoundException $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => 'Rubro no encontrado',
         ], 404);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'code' => 500,
            'message' => 'Error en el servidor: ' . $e->getMessage()
         ], 500);
      }
   }
}
