<?php

namespace App\Http\Controllers;

use App\Http\Requests\Asociado\CreateAsociadoRequest;
use App\Http\Requests\Asociado\UpdateAsociadoRequest;
use App\Http\Requests\Asociado\UpdateImageAsociadoRequest;
use App\Http\Requests\Asociado\UpdateStateAsociadoRequest;
use App\Models\Asociado;
use App\Services\AsociadoService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class AsociadoController extends Controller
{

   public function __construct(protected AsociadoService $service) {}

   public function create(CreateAsociadoRequest $request): JsonResponse
   {
      try {
         $this->service->create($request->validated());
         return response()->json([
            'status' => true,
            'message' => 'Asociado creado exitosamente',
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

   public function changeImagen(UpdateImageAsociadoRequest $request, int $id): JsonResponse
   {
      try {
         $this->service->changeImagen($request->file('imagen'), $id);
         return response()->json([
            'status' => true,
            'message' => 'Imagen actualizada exitosamente',
         ], 200);
      } catch (ModelNotFoundException $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => 'Asociado no encontrado',
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

   public function getAsociados(): JsonResponse
   {
      try {
         $response = $this->service->getAsociados();
         return response()->json($response);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage()
         ], 500);
      }
   }

   public function getAll(Request $request): JsonResponse
   {
      try {
         $response = $this->service->getAll($request->all());
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

   public function asociadoWithFamiliars(int $id): JsonResponse
   {
      try {
         $asociado = $this->service->asociadoWithFamiliars($id);
         return response()->json($asociado);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage()
         ], 500);
      }
   }

   public function update(UpdateAsociadoRequest $request, Asociado $asociado): JsonResponse
   {
      try {
         $this->service->update($request->validated(), $asociado);
         return response()->json([
            'status' => true,
            'message' => 'Asociado actualizado correctamente.',
         ]);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage()
         ], 500);
      }
   }

   public function changeStatus(UpdateStateAsociadoRequest $request, int $id): JsonResponse
   {
      try {
         $this->service->changeStatus($request->validated(), $id);
         return response()->json([
            'status' => true,
            'message' => 'Estado actualizado exitosamente',
         ], 200);
      } catch (ModelNotFoundException $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => 'Asociado no encontrado',
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

   public function delete(int $id): JsonResponse
   {
      try {
         $this->service->delete($id);
         return response()->json([
            'status' => true,
            'message' => 'Asociado eliminado exitosamente',
         ], 200);
      } catch (ModelNotFoundException $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => 'Asociado no encontrado',
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

   public function deleteImagen(int $id): JsonResponse
   {
      try {
         $this->service->deleteImagen($id);
         return response()->json([
            'status' => true,
            'message' => 'Imagen eliminada exitosamente',
         ], 200);
      } catch (ModelNotFoundException $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => 'Asociado no encontrado',
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
