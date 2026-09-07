<?php

namespace App\Http\Controllers;

use App\Http\Requests\Empleado\CreateEmpleadoRequest;
use App\Http\Requests\Empleado\UpdateEmpleadoRequest;
use App\Http\Requests\Empleado\UpdateImageEmpleadoRequest;
use App\Models\Empleado;
use App\Services\EmpleadoService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class EmpleadoController extends Controller
{

   public function __construct(protected EmpleadoService $service) {}

   public function create(CreateEmpleadoRequest $request): JsonResponse
   {
      try {
         $this->service->create($request->validated());
         return response()->json([
            'status' => true,
            'message' => 'Empleado creado exitosamente',
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

   public function changeImagen(UpdateImageEmpleadoRequest $request, int $id): JsonResponse
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
            'message' => 'Empleado no encontrado',
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

   public function update(UpdateEmpleadoRequest $request, Empleado $empleado): JsonResponse
   {
      try {
         $this->service->update($request->validated(), $empleado);
         return response()->json([
            'status' => true,
            'message' => 'Empleado actualizado correctamente.',
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
            'message' => 'Empleado eliminado exitosamente',
         ], 200);
      } catch (ModelNotFoundException $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => 'Empleado no encontrado',
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
            'message' => 'Empleado no encontrado',
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
