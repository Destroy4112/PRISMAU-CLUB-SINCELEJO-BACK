<?php

namespace App\Http\Controllers;

use App\Http\Requests\Menu\CreateMenuRequest;
use App\Http\Requests\Menu\UpdateMenuRequest;
use App\Services\MenuService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Throwable;

class MenuController extends Controller
{

   public function __construct(protected MenuService $service) {}

   public function create(CreateMenuRequest $request): JsonResponse
   {
      try {
         $this->service->create($request->all());
         return response()->json([
            "status" => true,
            "message" => "Menu creado con exito"
         ], 201);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            "status" => false,
            "message" => $e->getMessage()
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
            "status" => false,
            "message" => $e->getMessage()
         ], 500);
      }
   }

   public function update(UpdateMenuRequest $request, int $id)
   {
      try {
         $this->service->update($request->all(), $id);
         return response()->json([
            "status" => true,
            "message" => "Menu actualizado con exito"
         ], 200);
      } catch (ModelNotFoundException $e) {
         report($e);
         return response()->json([
            "status" => false,
            "message" => "Menu no encontrado"
         ], 404);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            "status" => false,
            "message" => $e->getMessage()
         ], 500);
      }
   }

   public function delete(int $id)
   {
      try {
         $this->service->delete($id);
         return response()->json([
            "status" => true,
            "message" => "Menu eliminado con exito"
         ], 200);
      } catch (ModelNotFoundException $e) {
         report($e);
         return response()->json([
            "status" => false,
            "message" => "Menu no encontrado"
         ], 404);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            "status" => false,
            "message" => $e->getMessage()
         ], 500);
      }
   }
}
