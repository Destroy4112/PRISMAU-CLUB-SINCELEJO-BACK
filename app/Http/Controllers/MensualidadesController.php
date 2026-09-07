<?php

namespace App\Http\Controllers;

use App\Http\Requests\Mensualidad\GeneratePreferenceRequest;
use App\Http\Requests\Mensualidad\PayMensualidadRequest;
use App\Services\MensualidadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use Throwable;

class MensualidadesController extends Controller
{
   public function __construct(protected MensualidadService $service,) {}

   public function pay(PayMensualidadRequest $request): JsonResponse
   {
      try {
         $factura = $this->service->getFactura($request->mensualidad_id);

         $soporteUrl = $this->service->guardarSoporte($request->file('soporte'), $factura);

         $valorDiferente = $request->boolean('valor_diferente');

         $resultado = $this->service->aplicarPagoMensualidades([
            'mensualidad_id' => $factura->id,
            'monto' => $valorDiferente ? $request->valor : null,
            'aplicar_desde_mas_antigua' => $valorDiferente,
            'metodo_pago' => $request->metodo_pago,
            'referencia_pago' => $request->referencia_pago,
            'soporte' => $soporteUrl,
            'fecha_pago' => now(),
         ]);

         return response()->json($resultado, 200);
      } catch (\Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage()
         ], 500);
      }
   }

   public function createPreference(GeneratePreferenceRequest $request)
   {
      try {
         $data = $request->validated();
         MercadoPagoConfig::setAccessToken(config('mercadopago.access_token'));

         $valorDiferente = (bool) $data['valor_diferente'];

         $resultado = $this->service->createPreference([
            'mensualidad_id' => $data['mensualidad_id'],
            'monto' => $valorDiferente ? $data['valor'] : null,
            'aplicar_desde_mas_antigua' => $valorDiferente,
         ]);

         if (!$resultado['status']) {
            return response()->json($resultado, 200);
         }

         $pref = $resultado['data'];

         $cliente = new PreferenceClient();

         $frontUrl = rtrim(config('mercadopago.front_url'), '/');
         $backUrl = rtrim(config('mercadopago.back_url'), '/');

         $preference = $cliente->create([
            'external_reference' => (string) $pref['mensualidad_id'],
            'items' => [[
               'title' => 'Mensualidad ' . $pref['mensualidad_id'],
               'quantity' => 1,
               'unit_price' => $pref['monto_bruto'],
               'description' => 'Mensualidad',
            ]],
            'metadata' => [
               'tipo_pago' => 'Mensualidad',
               'gross_up' => true,
               'neto_deseado' => $pref['valor_neto'],
               'monto_bruto' => $pref['monto_bruto'],
               'deposito_estimado' => $pref['deposito_estimado'],
               'comision_base' => $pref['comision_base'],
               'impuesto_comision' => $pref['impuesto_comision'],
               'aplicar_desde_mas_antigua' => $pref['aplicar_desde_mas_antigua'],
            ],
            'back_urls' => [
               'success' => "{$frontUrl}/mensualidades?status=success",
               'failure' => "{$frontUrl}/mensualidades?status=failure",
               'pending' => "{$frontUrl}/mensualidades?status=pending",
            ],
            'auto_return' => 'approved',
            'notification_url' => "{$backUrl}/api/webhook",
         ]);

         return response()->json([
            'status' => true,
            'message' => 'Preferencia creada correctamente.',
            "data" => [
               'preference_id' => $preference->id,
               'init_point' => $preference->init_point,
               'neto_deseado' => $pref['valor_neto'],
               'monto_bruto' => $pref['monto_bruto'],
               'deposito_estimado' => $pref['deposito_estimado'],
               'aplicar_desde_mas_antigua' => $pref['aplicar_desde_mas_antigua'],
            ]
         ], 200);
      } catch (MPApiException $exception) {
         $apiResponse = $exception->getApiResponse();

         Log::error('Error de API de Mercado Pago', [
            'status_code' => $apiResponse?->getStatusCode(),
            'content' => $apiResponse?->getContent(),
         ]);

         return response()->json([
            'status' => false,
            'message' => 'Mercado Pago rechazó la solicitud.',
            'error' => app()->isLocal()
               ? $apiResponse?->getContent()
               : null,
         ], $apiResponse?->getStatusCode() ?? 500);
      } catch (Throwable $exception) {
         Log::error('Error interno creando preferencia', [
            'message' => $exception->getMessage(),
         ]);

         return response()->json([
            'status' => false,
            'message' => 'No fue posible crear la preferencia.',
         ], 500);
      }
   }

   public function getByDocumento(Request $request, string $documento): JsonResponse
   {
      try {
         $res = $this->service->getByDocumento($request->all(), $documento);
         return response()->json($res, 200);
      } catch (\Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage()
         ], 500);
      }
   }

   public function resumePaymentMercadoPago(string $paymentId): JsonResponse
   {
      try {
         $resultado = $this->service->getResumePaymentReference($paymentId);
         return response()->json($resultado, 200);
      } catch (\Throwable $e) {
         report($e);
         return response()->json([
            'status' => false,
            'message' => $e->getMessage()
         ], 500);
      }
   }
}
