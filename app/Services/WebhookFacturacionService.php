<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookFacturacionService
{
   protected string $accessToken;

   public function __construct(
      protected MensualidadService $mensualidadService,
      protected CuotaBaileService $cuotaBaileService
   ) {
      $this->accessToken = config('mercadopago.access_token');
   }

   public function handleWebhook(array $data, ?string $queryType = null): array
   {
      $type = $data['type'] ?? $queryType ?? null;

      if ($type !== 'payment') {
         return [
            'status' => false,
            'message' => 'Evento no válido',
            'code' => 400,
         ];
      }

      $paymentId = $data['data']['id'] ?? $data['id'] ?? null;

      if (!$paymentId) {
         return [
            'status' => false,
            'message' => 'ID de pago faltante',
            'code' => 400,
         ];
      }

      $respuesta = $this->consultarPagoMercadoPago((string) $paymentId);

      if (!$respuesta) {
         return [
            'status' => false,
            'message' => 'No se pudo consultar el pago en Mercado Pago',
            'code' => 400,
         ];
      }

      $tipo = $respuesta['metadata']['tipo_pago'] ?? null;

      if (!$tipo && isset($respuesta['additional_info']['items'][0]['description'])) {
         $tipo = $respuesta['additional_info']['items'][0]['description'];
      }

      if (!$tipo) {
         return [
            'status' => false,
            'message' => 'Tipo de pago no reconocido',
            'code' => 400,
         ];
      }

      if (str_contains($tipo, 'Mensualidad')) {
         return $this->mensualidadService->webhookMensualidades((string) $paymentId, $respuesta);
      }

      if (str_contains($tipo, 'Cuota')) {
         Log::info('Cuota Baile');
         return $this->cuotaBaileService->webhookCuotasBaile((string) $paymentId, $respuesta);
      }

      return [
         'status' => false,
         'message' => 'Tipo de pago no reconocido',
         'code' => 400,
      ];
   }

   private function consultarPagoMercadoPago(string $paymentId): ?array
   {
      $response = Http::withToken($this->accessToken)
         ->get("https://api.mercadopago.com/v1/payments/{$paymentId}");

      if (!$response->successful()) {
         Log::error('Error consultando pago en Mercado Pago', [
            'payment_id' => $paymentId,
            'status' => $response->status(),
            'body' => $response->json(),
         ]);

         return null;
      }

      return $response->json();
   }
}
