<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

   public function __construct(protected DashboardService $dashboardService) {}

   public function stats(Request $request): JsonResponse
   {
      try {
         $user = $request->user();
         return response()->json($this->dashboardService->getStats($user));
      } catch (\Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }
}
