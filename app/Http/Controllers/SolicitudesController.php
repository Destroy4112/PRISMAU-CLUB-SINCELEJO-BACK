<?php

namespace App\Http\Controllers;

use App\Http\Requests\Solicitud\CreateSolicitudRequest;
use App\Http\Requests\Solicitud\ReplySolicitudRequest;
use App\Services\SolicitudesService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class SolicitudesController extends Controller
{

   public function __construct(protected SolicitudesService $service) {}

   public function create(CreateSolicitudRequest $request): JsonResponse
   {
      try {
         $this->service->create($request->validated());
         return response()->json([
            'status' => true,
            'message' => 'Solicitud creada exitosamente',
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

   public function get(int $id): JsonResponse
   {
      try {
         $res = $this->service->get($id);
         return response()->json($res);
      } catch (ModelNotFoundException $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => 'Solicitud no encontrada',
         ], 404);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function getByUser(int $id, Request $request): JsonResponse
   {
      try {
         $paginator = $this->service->getByUser($id, $request->all());
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

   public function contPendientes(): JsonResponse
   {
      try {
         $res = $this->service->contPendientes();
         return response()->json($res);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function contByUser(int $id): JsonResponse
   {
      try {
         $res = $this->service->contByUser($id);
         return response()->json($res);
      } catch (Throwable $e) {
         report($e);

         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function reply(ReplySolicitudRequest $request, int $id): JsonResponse
   {
      try {
         $this->service->reply($request->validated(), $id);
         return response()->json([
            'status' => true,
            'message' => 'Solicitud respondida con exito',
         ], 201);
      } catch (ModelNotFoundException $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => 'Solicitud no encontrada',
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
