<?php

namespace App\Http\Controllers;

use App\Http\Requests\Usuario\ChangePasswordRequest;
use App\Services\UserService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class UsuarioController extends Controller
{

   public function __construct(protected  UserService $service) {}

   public function getSociosForPayments(Request $request): JsonResponse
   {
      try {
         $res = $this->service->getSociosForPayments($request->all());
         return response()->json([
            'data'  => $res->items(),
            'total' => $res->total(),
            'page'  => $res->currentPage(),
            'limit' => $res->perPage(),
         ]);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function getSaldosSocios(Request $request): JsonResponse
   {
      try {
         $response = $this->service->getSaldosSocios($request->all());
         return response()->json([
            'data'  => $response->items(),
            'total' => $response->total(),
            'page'  => $response->currentPage(),
            'limit' => $response->perPage(),
         ], 200);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function getContabilidadGeneral(): JsonResponse
   {
      try {
         return response()->json($this->service->getContabilidadGeneral(), 200);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function getByDocumento(string $documento): JsonResponse
   {
      try {
         $res = $this->service->getByDocumento($documento);
         return response()->json($res);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function changePassword(ChangePasswordRequest $request, int $id): JsonResponse
   {
      try {
         $this->service->changePassword($request->validated(), $id);

         return response()->json([
            'status' => true,
            'message' => 'Contraseña actualizada correctamente',
         ], 200);
      } catch (ModelNotFoundException $e) {
         return response()->json([
            'status' => false,
            'message' => 'Usuario no encontrado',
         ], 404);
      } catch (Throwable $e) {
         report($e);

         return response()->json([
            'status' => false,
            'message' => 'Ocurrió un error al cambiar la contraseña',
         ], 500);
      }
   }

   public function resetPassword(int $id): JsonResponse
   {
      try {
         $this->service->resetPassword($id);
         return response()->json([
            'status' => true,
            'message' => 'Contraseña reseteada correctamente',
         ], 200);
      } catch (ModelNotFoundException $e) {
         return response()->json([
            'status' => false,
            'message' => 'Usuario no encontrado',
         ], 404);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function eliminarCuenta(int $id)
   {
      $response = $this->service->eliminarCuenta($id);
      return response()->json($response);
   }
}
