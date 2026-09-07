<?php

namespace App\Http\Controllers;

use App\Http\Requests\Familiar\CreateFamiliarRequest;
use App\Http\Requests\Familiar\UpdateFamiliarRequest;
use App\Http\Requests\Familiar\UpdateImageFamiliarRequest;
use App\Models\Familiar;
use App\Services\FamiliarService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Throwable;

class FamiliarController extends Controller
{

    public function __construct(protected FamiliarService $service) {}

    public function create(CreateFamiliarRequest $request): JsonResponse
    {
        try {
            $this->service->create($request->validated());
            return response()->json([
                'status' => true,
                'message' => 'Familiar creado exitosamente',
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

    public function changeImagen(UpdateImageFamiliarRequest $request, int $id): JsonResponse
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
                'message' => 'Familiar no encontrado',
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

    public function get(int $id, string $rol): JsonResponse
    {
        try {
            $familiares = $this->service->get($id, $rol);
            return response()->json($familiares);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function nucleoDesdeEsposa(int $id): JsonResponse
    {
        try {
            $nucleo = $this->service->nucleoDesdeEsposa($id);
            return response()->json($nucleo);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function contSocio(int $id, string $rol): JsonResponse
    {
        try {
            $familiar = $this->service->contSocio($id, $rol);
            return response()->json($familiar);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateFamiliarRequest $request, Familiar $Familiar): JsonResponse
    {
        try {
            $this->service->update($request->validated(), $Familiar);
            return response()->json([
                'status' => true,
                'message' => 'Familiar actualizado correctamente.',
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
                'message' => 'Familiar eliminado exitosamente',
            ], 200);
        } catch (ModelNotFoundException $e) {
            report($e);
            return response()->json([
                'status' => false,
                'message' => 'Familiar no encontrado',
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
                'message' => 'Familiar no encontrado',
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
