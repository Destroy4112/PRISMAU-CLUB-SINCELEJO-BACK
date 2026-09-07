<?php

namespace App\Http\Controllers;

use App\Http\Requests\Noticia\CreateNoticiaRequest;
use App\Http\Requests\Noticia\UpdateNoticiaRequest;
use App\Models\Noticia;
use App\Services\NoticiasService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Throwable;

class NoticiaController extends Controller
{

   public function __construct(protected NoticiasService $service) {}

   public function create(CreateNoticiaRequest $request): JsonResponse
   {
      try {
         $this->service->create($request->validated());
         return response()->json([
            'status' => true,
            'message' => 'Evento creado exitosamente',
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

   public function update(UpdateNoticiaRequest $request, Noticia $noticia): JsonResponse
   {
      try {
         $this->service->update($request->validated(), $noticia);
         return response()->json([
            'status' => true,
            'message' => 'Evento actualizado correctamente.',
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
            'message' => 'Evento eliminado exitosamente',
         ], 200);
      } catch (ModelNotFoundException $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => 'Evento no encontrado',
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
