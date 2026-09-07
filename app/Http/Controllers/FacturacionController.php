<?php

namespace App\Http\Controllers;

use App\Http\Requests\Facturacion\GenerateBillyingRequest;
use App\Http\Requests\Facturacion\UpdateBillyingValueRequest;
use App\Services\FacturacionService;
use Illuminate\Http\JsonResponse;
use Throwable;

class FacturacionController extends Controller
{
    public function __construct(protected FacturacionService $service) {}

    public function generate(GenerateBillyingRequest $request): JsonResponse
    {
        try {
            $response = $this->service->generate($request->validated());
            return response()->json($response);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateBillyingsValue(UpdateBillyingValueRequest $request): JsonResponse
    {
        try {
            $response = $this->service->updateBillyingsValue($request->validated());
            return response()->json($response);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
