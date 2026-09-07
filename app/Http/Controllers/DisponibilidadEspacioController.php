<?php

namespace App\Http\Controllers;

use App\Http\Requests\DisponibilidadEspacio\SaveDisponibilidadEspacioRequest;
use App\Services\DisponibilidadEspacioService;
use Illuminate\Http\JsonResponse;
use Throwable;

class DisponibilidadEspacioController extends Controller
{

   public function __construct(protected DisponibilidadEspacioService $service) {}

   public function save(SaveDisponibilidadEspacioRequest $request): JsonResponse
   {
      try {
         $data = $request->validated();
         $this->service->save($data['espacio_id'], $data['disponibilidades'],);
         return response()->json([
            'status' => true,
            'message' => 'Disponibilidad creada correctamente',
         ]);
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
         $res = $this->service->getByEspacio($id);
         return response()->json($res);
      } catch (Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }
}
