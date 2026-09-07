<?php

namespace App\Http\Controllers;

use App\Http\Requests\Encuesta\CreateEncuestaRequest;
use App\Http\Requests\Encuesta\SaveReplyRequest;
use App\Http\Requests\Encuesta\UpdateEncuestaRequest;
use App\Services\EncuestasService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Throwable;

class EncuestasController extends Controller
{

   public function __construct(protected EncuestasService $service) {}

   public function create(CreateEncuestaRequest $request): JsonResponse
   {
      try {
         $this->service->create($request->validated());
         return response()->json([
            'status' => true,
            'message' => 'Encuesta creada exitosamente',
         ], 201);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function saveReplies(SaveReplyRequest $request, int $id): JsonResponse
   {
      try {
         $res = $this->service->saveReplies($request->validated(), $id);
         return response()->json($res);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function getAll(): JsonResponse
   {
      try {
         $res = $this->service->getAll();
         return response()->json($res);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function usersReply(int $id): JsonResponse
   {
      try {
         $res = $this->service->usersReply($id);
         return response()->json($res);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function getAvailable(int $id): JsonResponse
   {
      try {
         $res = $this->service->getAvailable($id);
         return response()->json($res);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function get(int $id): JsonResponse
   {
      try {
         $res = $this->service->get($id);
         return response()->json($res);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function update(UpdateEncuestaRequest $request, int $id): JsonResponse
   {
      try {
         $this->service->update($request->validated(), $id);
         return response()->json([
            'status' => true,
            'message' => 'Encuesta actualizada exitosamente',
         ], 200);
      } catch (ModelNotFoundException $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => 'Encuesta no encontrada',
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
            'message' => 'Encuesta eliminada exitosamente',
         ], 200);
      } catch (ModelNotFoundException $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => 'Encuesta no encontrada',
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
