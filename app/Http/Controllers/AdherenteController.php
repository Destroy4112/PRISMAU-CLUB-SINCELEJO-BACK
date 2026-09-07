<?php

namespace App\Http\Controllers;

use App\Http\Requests\Adherente\CreateAdherenteRequest;
use App\Http\Requests\Adherente\UpdateAdherenteRequest;
use App\Http\Requests\Adherente\UpdateImageAdherenteRequest;
use App\Http\Requests\Adherente\UpdateStateAdherenteRequest;
use App\Models\Adherente;
use App\Services\AdherenteService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class AdherenteController extends Controller
{

   public function __construct(protected AdherenteService $service) {}

   public function create(CreateAdherenteRequest $request): JsonResponse
   {
      try {
         $this->service->create($request->validated());
         return response()->json([
            'status' => true,
            'message' => 'Adherente creado exitosamente',
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

   public function changeImagen(UpdateImageAdherenteRequest $request, int $id): JsonResponse
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
            'message' => 'Adherente no encontrado',
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

   public function adherenteWithFamiliars(int $id): JsonResponse
   {
      try {
         $adherente = $this->service->adherenteWithFamiliars($id);
         return response()->json($adherente);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage()
         ], 500);
      }
   }

   public function update(UpdateAdherenteRequest $request, Adherente $adherente): JsonResponse
   {
      try {
         $this->service->update($request->validated(), $adherente);
         return response()->json([
            'status' => true,
            'message' => 'Adherente actualizado correctamente.',
         ]);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage()
         ], 500);
      }
   }

   public function changeStatus(UpdateStateAdherenteRequest $request, int $id): JsonResponse
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
            'message' => 'Adherente no encontrado',
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

   public function changeToAsociado(int $id): JsonResponse
   {
      try {
         $this->service->changeToAsociado($id);
         return response()->json([
            'status' => true,
            'message' => 'Cambio realizado exitosamente',
         ], 200);
      } catch (ModelNotFoundException $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => 'Adherente no encontrado',
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
            'message' => 'Adherente eliminado exitosamente',
         ], 200);
      } catch (ModelNotFoundException $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => 'Adherente no encontrado',
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
            'message' => 'Adherente no encontrado',
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
