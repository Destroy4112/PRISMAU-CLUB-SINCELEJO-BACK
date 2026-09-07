<?php

namespace App\Http\Controllers;

use App\Http\Requests\Espacio\CreateEspacioRequest;
use App\Http\Requests\Espacio\UpdateEspacioRequest;
use App\Models\Espacio;
use App\Services\EspacioService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class EspacioController extends Controller
{

   public function __construct(protected EspacioService $service) {}

   public function create(CreateEspacioRequest $request): JsonResponse
   {
      try {
         $this->service->create($request->validated(), $request->file('imagen'));
         return response()->json([
            'status' => true,
            'message' => 'Espacio creado exitosamente',
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

   public function getPaginated(Request $request): JsonResponse
   {
      try {
         $response = $this->service->getPaginated($request->all());
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
            'message' => $e->getMessage()
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

   public function update(UpdateEspacioRequest $request, Espacio $espacio): JsonResponse
   {
      try {
         $this->service->update($request->validated(), $request->file('imagen'), $espacio);
         return response()->json([
            'status' => true,
            'message' => 'Espacio actualizado correctamente.',
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
            'message' => 'Espacio eliminado exitosamente',
         ], 200);
      } catch (ModelNotFoundException $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => 'Espacio no encontrado',
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
