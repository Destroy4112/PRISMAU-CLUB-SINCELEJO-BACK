<?php

namespace App\Http\Controllers;

use App\Services\WebhookFacturacionService;
use Illuminate\Http\Request;

class WebhookFacturacionController extends Controller
{

   public function __construct(protected WebhookFacturacionService $service) {}

   public function handleWebhook(Request $request)
   {
      try {
         $response = $this->service->handleWebhook($request->all());
         return response()->json($response);
      } catch (\Exception $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
         ], 500);
      }
   }
}
