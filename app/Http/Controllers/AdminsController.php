<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admins\CreateAdminRequest;
use App\Http\Requests\Admins\UpdateAdminRequest;
use App\Services\AdminService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class AdminsController extends Controller
{

   public function __construct(protected AdminService $service) {}

   public function create(CreateAdminRequest $request): JsonResponse
   {
      try {
         $this->service->create($request->validated());

         return response()->json([
            'status' => true,
            'message' => 'Administrador creado exitosamente',
         ], 201);
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
         $paginator = $this->service->getAll($request->all());

         return response()->json([
            'data'  => $paginator->items(),
            'total' => $paginator->total(),
            'page'  => $paginator->currentPage(),
            'limit' => $paginator->perPage(),
         ], 200);
      } catch (Throwable $e) {
         report($e);

         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function update(UpdateAdminRequest $request, int $id): JsonResponse
   {
      try {
         $this->service->update($request->validated(), $id);

         return response()->json([
            'status' => true,
            'message' => 'Administrador actualizado exitosamente',
         ], 200);
      } catch (ModelNotFoundException $e) {
         return response()->json([
            'status' => false,
            'message' => 'Administrador no encontrado',
         ], 404);
      } catch (Throwable $e) {
         report($e);

         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function changeStatus(int $id): JsonResponse
   {
      try {
         $this->service->changeStatus($id);

         return response()->json([
            'status' => true,
            'message' => 'Estado actualizado exitosamente',
         ], 200);
      } catch (ModelNotFoundException $e) {
         return response()->json([
            'status' => false,
            'message' => 'Administrador no encontrado',
         ], 404);
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
            'message' => 'Administrador eliminado exitosamente',
         ], 200);
      } catch (ModelNotFoundException $e) {
         return response()->json([
            'status' => false,
            'message' => 'Administrador no encontrado',
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
