<?php

namespace App\Http\Controllers;

use App\Http\Requests\Invitado\CreateInvitadoRequest;
use App\Http\Requests\Invitado\SaveImageRequest;
use App\Services\InvitadosService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class InvitadoController extends Controller
{

   public function __construct(protected InvitadosService $service) {}

   public function create(CreateInvitadoRequest $request): JsonResponse
   {
      try {
         $response = $this->service->create($request->validated());
         return response()->json($response, $response['code'] ?? 200);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function saveImagen(SaveImageRequest $request, int $id): JsonResponse
   {
      try {
         $data = $request->validated();
         $this->service->saveImagen($data['imagen'], $id);
         return response()->json([
            'status' => true,
            'message' => 'Imagen guardada',
         ]);
      } catch (ModelNotFoundException $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => 'Invitado no encontrado',
         ], 404);
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
         $invitados = $this->service->getAll($request->all());
         return response()->json($invitados);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function getEntradas(Request $request): JsonResponse
   {
      try {
         $response = $this->service->getEntradas($request->all());
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
            'message' => $e->getMessage(),
         ], 500);
      }
   }

   public function updateEntrada(int $id): JsonResponse
   {
      try {
         $this->service->updateEntrada($id);
         return response()->json([
            'status' => true,
            'message' => 'Entrada actualizada',
         ]);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }
}
