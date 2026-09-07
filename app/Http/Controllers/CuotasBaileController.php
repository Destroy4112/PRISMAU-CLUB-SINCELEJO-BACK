<?php

namespace App\Http\Controllers;

use App\Http\Requests\CuotaBaile\GeneratePreferenceRequest;
use App\Http\Requests\CuotaBaile\PayCuotaBaileRequest;
use App\Services\CuotaBaileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use Throwable;

class CuotasBaileController extends Controller
{

   public function __construct(protected CuotaBaileService $service,) {}

   public function pay(PayCuotaBaileRequest $request): JsonResponse
   {
      try {
         $factura = $this->service->getFactura($request->cuotas_baile_id);

         $soporteUrl = $this->service->guardarSoporte($request->file('soporte'), $factura);

         $resultado = $this->service->aplicarPagoCuotasBaile([
            'cuotas_baile_id' => $factura->id,
            'monto' => $request->boolean('valor_diferente') ? $request->valor : null,
            'aplicar_desde_mas_antigua' => $request->boolean('valor_diferente'),
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
            'cuotas_baile_id' => $data['cuotas_baile_id'],
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
            'external_reference' => (string) $pref['cuotas_baile_id'],
            'items' => [[
               'title' => 'Cuota Baile ' . $pref['cuotas_baile_id'],
               'quantity' => 1,
               'unit_price' => $pref['monto_bruto'],
               'description' => 'Cuota',
            ]],
            'metadata' => [
               'tipo_pago' => 'Cuota',
               'gross_up' => true,
               'neto_deseado' => $pref['valor_neto'],
               'monto_bruto' => $pref['monto_bruto'],
               'deposito_estimado' => $pref['deposito_estimado'],
               'comision_base' => $pref['comision_base'],
               'impuesto_comision' => $pref['impuesto_comision'],
               'aplicar_desde_mas_antigua' => $pref['aplicar_desde_mas_antigua'],
            ],
            'back_urls' => [
               'success' => "{$frontUrl}/cuotas-baile?status=success",
               'failure' => "{$frontUrl}/cuotas-baile?status=failure",
               'pending' => "{$frontUrl}/cuotas-baile?status=pending",
            ],
            'auto_return' => 'approved',
            'notification_url' => "{$backUrl}/api/webhook",
         ]);

         return response()->json([
            'status' => true,
            'message' => 'Preferencia creada correctamente.',
            'data' => [
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
