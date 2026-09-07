<?php

namespace App\Http\Controllers;

use App\Http\Requests\MenuRol\AsignMenuRolRequest;
use App\Services\MenuRolService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Throwable;

class MenuRoleController extends Controller
{

   public function __construct(protected MenuRolService $service) {}

   public function assign(AsignMenuRolRequest $request): JsonResponse
   {
      try {
         $this->service->assign($request->validated());

         return response()->json([
            'status' => true,
            'message' => 'Módulo asignado correctamente',
         ], 201);
      } catch (Throwable $e) {
         report($e);

         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function getByRole(int $id): JsonResponse
   {
      try {
         $response = $this->service->getByRole($id);
         return response()->json($response);
      } catch (Throwable $e) {
         report($e);

         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function getByMenuType(int $id): JsonResponse
   {
      try {
         $response = $this->service->getByMenuType($id);
         return response()->json($response);
      } catch (Throwable $e) {
         report($e);

         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function delete(int $id): JsonResponse
   {
      try {
         $this->service->delete($id);

         return response()->json([
            'status' => true,
            'message' => 'Módulo eliminado de rol correctamente',
         ], 200);
      } catch (ModelNotFoundException $e) {
         return response()->json([
            'status' => false,
            'message' => 'MenuRol no encontrado',
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
