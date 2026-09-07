<?php

namespace App\Services;

use App\Models\CuotasBaile;
use App\Models\PagosCuotasBaile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CuotaBaileService
{

   public function __construct(
      protected UserService $userService
   ) {}

   public function generateBillying(array $data): array
   {
      $anio = $data['anio'];
      $cantidadCuotas = $data['cuotas'];
      $valor = $data['valor'];

      $usuarios = User::query()->whereIn('Rol', [2, 3])->pluck('id');

      if ($usuarios->isEmpty()) {
         return [
            'status' => false,
            'message' => 'No se encontraron usuarios para generar cuotas de baile.',
         ];
      }

      $descripciones = collect(range(1, $cantidadCuotas))->mapWithKeys(
         fn($numero) => [$numero => "Cuota {$numero} de {$anio}",]
      );

      $cuotasExistentes = CuotasBaile::query()
         ->whereIn('user_id', $usuarios)
         ->whereIn('descripcion', $descripciones->values())
         ->get(['user_id', 'descripcion'])
         ->groupBy('user_id')
         ->map(
            fn($cuotas) => $cuotas
               ->pluck('descripcion')
               ->all()
         );

      $creadas = 0;

      foreach ($usuarios as $userId) {
         $existentesUsuario = $cuotasExistentes->get(
            $userId,
            []
         );

         foreach ($descripciones as $descripcion) {
            if (in_array(
               $descripcion,
               $existentesUsuario,
               true
            )) {
               continue;
            }

            CuotasBaile::create([
               'user_id' => $userId,
               'descripcion' => $descripcion,
               'valor' => $valor,
               'estado' => false,
            ]);

            $creadas++;
         }
      }

      if ($creadas === 0) {
         return [
            'status' => false,
            'message' => "Todos los usuarios ya tienen las {$cantidadCuotas} cuotas de baile del año {$anio}.",
         ];
      }

      return [
         'status' => true,
         'message' => "Cuotas de baile generadas correctamente.",
      ];
   }

   public function createPreference(array $data): array
   {
      $factura = CuotasBaile::query()->where('id', $data['cuotas_baile_id'])
         ->withSum('pagos as total_pagos', 'monto')->firstOrFail();

      $aplicarDesdeMasAntigua = (bool) ($data['aplicar_desde_mas_antigua'] ?? false);

      $deudas = $this->getDeudasPendientesSinLock($factura->user_id);

      if ($deudas->isEmpty()) {
         return [
            'status' => false,
            'message' => 'El usuario no tiene cuotas de baile pendientes.',
            'errors' => ['El usuario no tiene cuotas de baile pendientes.'],
         ];
      }

      if ($aplicarDesdeMasAntigua) {
         $cuotaBase = $deudas->first();

         $valorNetoDeseado = isset($data['monto']) && $data['monto'] !== null && $data['monto'] !== ''
            ? (float) $data['monto']
            : $this->calcularSaldoCuotaBaile($cuotaBase);
      } else {
         if ($factura->estado) {
            return [
               'status' => false,
               'message' => 'La cuota seleccionada ya se encuentra pagada.',
               'errors' => ['La cuota seleccionada ya se encuentra pagada.'],
            ];
         }

         $cuotaBase = $factura;
         $valorNetoDeseado = $this->calcularSaldoCuotaBaile($factura);
      }

      $saldoBase = $this->calcularSaldoCuotaBaile($cuotaBase);

      if ($valorNetoDeseado <= 0) {
         return [
            'status' => false,
            'message' => 'El valor a pagar debe ser mayor a cero.',
            'errors' => ['El valor a pagar debe ser mayor a cero.'],
         ];
      }

      if ($valorNetoDeseado < $saldoBase) {
         return [
            'status' => false,
            'message' => "El valor ingresado no cubre el saldo de la cuota de baile: {$saldoBase}.",
            'errors' => ["El valor ingresado no cubre el saldo de la cuota de baile: {$saldoBase}."],
         ];
      }

      if (!$aplicarDesdeMasAntigua && $valorNetoDeseado > $saldoBase) {
         return [
            'status' => false,
            'message' => 'El valor ingresado supera el saldo de la cuota seleccionada. Para pagar un valor diferente debe activar la opción correspondiente.',
            'errors' => ['El valor ingresado supera el saldo de la cuota seleccionada. Para pagar un valor diferente debe activar la opción correspondiente.'],
         ];
      }

      $totalDeuda = $deudas->sum(fn($cuota) => $this->calcularSaldoCuotaBaile($cuota));

      if ($aplicarDesdeMasAntigua && $valorNetoDeseado > $totalDeuda) {
         return [
            'status' => false,
            'message' => "El valor ingresado excede la deuda total del usuario: {$totalDeuda}.",
            'errors' => ["El valor ingresado excede la deuda total del usuario: {$totalDeuda}."],
         ];
      }

      $feePercent = 0.0329;
      $feeFixed = 800.0;
      $ivaFee = 0.19;

      $percentEff = $feePercent * (1 + $ivaFee);
      $fixedEff = $feeFixed * (1 + $ivaFee);

      if ($percentEff >= 1.0) {
         return [
            'status' => false,
            'message' => 'Configuración de comisión inválida.',
            'errors' => ['Configuración de comisión inválida.'],
         ];
      }

      $montoBruto = (float) ceil(($valorNetoDeseado + $fixedEff) / (1 - $percentEff));

      $comisionBase = $montoBruto * $feePercent + $feeFixed;
      $impuesto = $comisionBase * $ivaFee;
      $depositoEstimado = $montoBruto - $comisionBase - $impuesto;

      return [
         'status' => true,
         'message' => 'Valores de preferencia calculados correctamente.',
         'data' => [
            'cuotas_baile_id' => $factura->id,
            'user_id' => $factura->user_id,
            'valor_neto' => $valorNetoDeseado,
            'monto_bruto' => $montoBruto,
            'deposito_estimado' => (float) round($depositoEstimado, 0),
            'comision_base' => (float) round($comisionBase, 2),
            'impuesto_comision' => (float) round($impuesto, 2),
            'aplicar_desde_mas_antigua' => $aplicarDesdeMasAntigua,
         ],
      ];
   }

   public function webhookCuotasBaile(string $paymentId, array $respuesta): array
   {
      if (($respuesta['status'] ?? '') !== 'approved') {
         return [
            'status' => true,
            'message' => 'Pago no aprobado todavía',
            'code' => 200,
         ];
      }

      $externalReference = $respuesta['external_reference'] ?? null;

      if (!$externalReference) {
         return [
            'status' => false,
            'message' => 'Referencia externa faltante',
            'code' => 400,
         ];
      }

      $factura = CuotasBaile::query()->where('id', $externalReference)->first();

      if (!$factura) {
         return [
            'status' => false,
            'message' => 'Cuota de baile no encontrada',
            'code' => 404,
         ];
      }

      if (PagosCuotasBaile::query()->where('referencia_pago', $paymentId)->exists()) {
         return [
            'status' => true,
            'message' => 'Pago ya procesado',
            'code' => 200,
         ];
      }

      $metadata = $respuesta['metadata'] ?? [];

      $montoAplicar = isset($metadata['neto_deseado']) ? (float) $metadata['neto_deseado'] : 0;

      if ($montoAplicar <= 0) {
         return [
            'status' => false,
            'message' => 'No se encontró el valor neto a aplicar en el pago.',
            'code' => 400,
         ];
      }

      $aplicarDesdeMasAntigua = filter_var($metadata['aplicar_desde_mas_antigua'] ?? true, FILTER_VALIDATE_BOOLEAN);

      $fechaPago = isset($respuesta['date_created'])
         ? Carbon::parse($respuesta['date_created'])->format('Y-m-d H:i:s')
         : now();

      return $this->aplicarPagoCuotasBaile([
         'cuotas_baile_id' => $factura->id,
         'monto' => $montoAplicar,
         'aplicar_desde_mas_antigua' => $aplicarDesdeMasAntigua,
         'email' => $respuesta['payer']['email'] ?? null,
         'nombre' => trim(($respuesta['payer']['first_name'] ?? '') . ' ' . ($respuesta['payer']['last_name'] ?? '')),
         'identificacion' => $respuesta['payer']['identification']['number'] ?? null,
         'metodo_pago' => $respuesta['payment_method']['type'] ?? null,
         'referencia_pago' => $paymentId,
         'tarjeta' => $respuesta['card']['last_four_digits'] ?? null,
         'fecha_pago' => $fechaPago,
      ]);
   }

   public function updateValueSocio(string $documento, float $valor): array
   {
      $socio = User::where('Documento', $documento)->first();

      if (!$socio) {
         return [
            'status' => false,
            'message' => 'No se encontró un socio con ese documento.',
         ];
      }

      $cuotasPendientes = $socio->cuotas()->where('estado', false);

      if (!$cuotasPendientes->exists()) {
         return [
            'status' => false,
            'message' => 'No hay cuotas de baile pendientes para actualizar.',
         ];
      }

      $actualizadas = $cuotasPendientes->update(['valor' => $valor,]);

      return [
         'status' => true,
         'message' => 'Cuotas de baile actualizadas correctamente.',
         'actualizadas' => $actualizadas,
      ];
   }

   public function getByDocumento(array $filters, string $documento): array
   {
      $limit = min(max((int) ($filters['limit'] ?? 30), 1), 100);
      $search = isset($filters['search']) && $filters['search'] !== '' ? (int) $filters['search'] : null;
      $state = $filters['state'] ?? null;

      if ($state !== null && $state !== '') {
         $state = filter_var($state, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
      }

      $user = User::where('Documento', $documento)->firstOrFail();

      $baseQuery = $user->cuotas();

      $stats = [
         'total' => (clone $baseQuery)->count(),
         'pagadas' => (clone $baseQuery)->where('estado', true)->count(),
         'pendientes' => (clone $baseQuery)->where('estado', false)->count(),
      ];

      $cuotas = $user->cuotas()->with('pagos')
         ->when($search !== null, function ($query) use ($search) {
            $query->whereYear('año', $search);
         })
         ->when($state !== null, function ($query) use ($state) {
            $query->where('estado', $state);
         })
         ->orderBy('descripcion', 'desc')
         ->paginate($limit);

      return [
         'data' => $cuotas->items(),
         'total' => $cuotas->total(),
         'page' => $cuotas->currentPage(),
         'limit' => $cuotas->perPage(),
         'totalPages' => $cuotas->lastPage(),
         'stats' => $stats,
      ];
   }

   public function aplicarPagoCuotasBaile(array $data): array
   {
      return DB::transaction(function () use ($data) {

         $factura = CuotasBaile::query()
            ->where('id', $data['cuotas_baile_id'])
            ->withSum('pagos as pagos_sum_monto', 'monto')
            ->lockForUpdate()
            ->firstOrFail();

         $userId = $factura->user_id;

         $aplicarDesdeMasAntigua = (bool) ($data['aplicar_desde_mas_antigua'] ?? false);

         if (!$aplicarDesdeMasAntigua && $factura->estado) {
            return [
               'status' => false,
               'message' => 'La cuota seleccionada ya se encuentra pagada.',
            ];
         }

         $deudas = $this->getDeudasPendientesParaPago($userId);

         if ($deudas->isEmpty()) {
            return [
               'status' => false,
               'message' => 'El usuario no tiene cuotas de baile pendientes.',
            ];
         }

         if ($aplicarDesdeMasAntigua) {
            $cuotasAProcesar = $deudas;
            $cuotaBase = $deudas->first();
         } else {
            $cuotasAProcesar = collect([$factura]);
            $cuotaBase = $factura;
         }

         $saldoBase = $this->calcularSaldoCuotaBaile($cuotaBase);

         $montoPagado = isset($data['monto']) && $data['monto'] !== null && $data['monto'] !== ''
            ? (float) $data['monto']
            : $saldoBase;

         if ($montoPagado <= 0) {
            return [
               'status' => false,
               'message' => 'El monto pagado debe ser mayor a cero.',
            ];
         }

         if ($montoPagado < $saldoBase) {
            return [
               'status' => false,
               'message' => "El valor ingresado no cubre el saldo de la cuota de baile: {$saldoBase}.",
            ];
         }

         if (!$aplicarDesdeMasAntigua && $montoPagado > $saldoBase) {
            return [
               'status' => false,
               'message' => 'El valor ingresado supera el saldo de la cuota seleccionada. Para pagar un valor diferente debe activar la opción correspondiente.',
            ];
         }

         $totalDeuda = $deudas->sum(function ($cuotaBaile) {
            return $this->calcularSaldoCuotaBaile($cuotaBaile);
         });

         if ($aplicarDesdeMasAntigua && $montoPagado > $totalDeuda) {
            return [
               'status' => false,
               'message' => "El monto ingresado excede el total de la deuda pendiente del usuario: {$totalDeuda}.",
            ];
         }

         $montoRestante = $montoPagado;
         $pagosAplicados = [];

         foreach ($cuotasAProcesar as $cuotaBaile) {
            if ($montoRestante <= 0) {
               break;
            }

            $saldoCuotaBaile = $this->calcularSaldoCuotaBaile($cuotaBaile);

            if ($saldoCuotaBaile <= 0) {
               $cuotaBaile->update(['estado' => true]);
               continue;
            }

            $valorAplicado = min($montoRestante, $saldoCuotaBaile);

            PagosCuotasBaile::create([
               'cuotas_baile_id' => $cuotaBaile->id,
               'email' => $data['email'] ?? null,
               'nombre' => $data['nombre'] ?? null,
               'identificacion' => $data['identificacion'] ?? null,
               'metodo_pago' => $data['metodo_pago'] ?? null,
               'referencia_pago' => $data['referencia_pago'] ?? null,
               'monto' => $valorAplicado,
               'tarjeta' => $data['tarjeta'] ?? null,
               'soporte' => $data['soporte'] ?? null,
               'fecha_pago' => $data['fecha_pago'] ?? now(),
            ]);

            $montoRestante -= $valorAplicado;

            $nuevoSaldo = $saldoCuotaBaile - $valorAplicado;

            if ($nuevoSaldo <= 0) {
               $cuotaBaile->update(['estado' => true]);
               $cuotaId = $cuotaBaile->id;

               DB::afterCommit(function () use ($userId, $cuotaId) {
                  $this->userService->confirmarPagoCuotaBaile($userId, $cuotaId, 'Aprobado');
               });
            }

            $pagosAplicados[] = [
               'cuotas_baile_id' => $cuotaBaile->id,
               'descripcion' => $cuotaBaile->descripcion,
               'saldo_anterior' => $saldoCuotaBaile,
               'valor_aplicado' => $valorAplicado,
               'saldo_nuevo' => max($nuevoSaldo, 0),
               'pagada' => $nuevoSaldo <= 0,
            ];
         }

         return [
            'status' => true,
            'message' => 'Pago procesado correctamente.',
            'data' => [
               'user_id' => $userId,
               'monto_pagado' => $montoPagado,
               'monto_restante' => $montoRestante,
               'aplicar_desde_mas_antigua' => $aplicarDesdeMasAntigua,
               'pagos_aplicados' => $pagosAplicados,
            ],
         ];
      });
   }

   public function getFactura(int $id): CuotasBaile
   {
      return CuotasBaile::findOrFail($id);
   }

   public function getResumePaymentReference(string $referenciaPago): array
   {
      $pagos = PagosCuotasBaile::query()
         ->with([
            'cuota' => function ($query) {
               $query->with('pagos');
            }
         ])
         ->where('referencia_pago', $referenciaPago)
         ->orderBy('id', 'asc')
         ->get();

      if ($pagos->isEmpty()) {
         return [
            'status' => false,
            'message' => 'El pago aún no ha sido procesado o no se encontró información.',
            'errors' => ['El pago aún no ha sido procesado o no se encontró información.'],
         ];
      }

      $pagosAplicados = $pagos->map(function ($pago) {
         $cuota = $pago->cuota;

         $valorAplicado = (float) $pago->monto;

         $saldoNuevo = $cuota ? (float) $cuota->restante : 0;

         $saldoAnterior = $saldoNuevo + $valorAplicado;

         return [
            'cuotas_baile_id' => $cuota?->id,
            'descripcion' => $cuota?->descripcion,
            'saldo_anterior' => $saldoAnterior,
            'valor_aplicado' => $valorAplicado,
            'saldo_nuevo' => $saldoNuevo,
            'pagada' => (bool) ($cuota?->estado ?? false),
         ];
      })->values()->all();

      return [
         'status' => true,
         'message' => 'Resumen del pago obtenido correctamente',
         'data' => [
            'user_id' => $pagos->first()?->cuota?->user_id,
            'monto_pagado' => (float) $pagos->sum('monto'),
            'monto_restante' => 0,
            'aplicar_desde_mas_antigua' => count($pagosAplicados) > 1,
            'pagos_aplicados' => $pagosAplicados,
         ],
      ];
   }

   //-------------------------- Helpers ---------------------------------------

   private function getDeudasPendientesParaPago(int $userId): Collection
   {
      return CuotasBaile::query()
         ->where('user_id', $userId)
         ->where('estado', false)
         ->withSum('pagos as pagos_sum_monto', 'monto')
         ->orderBy('descripcion', 'asc')
         ->lockForUpdate()
         ->get();
   }

   private function calcularSaldoCuotaBaile(CuotasBaile $cuotaBaile): float
   {
      return max((float) $cuotaBaile->valor - (float) ($cuotaBaile->pagos_sum_monto ?? 0), 0);
   }

   public function guardarSoporte(?UploadedFile $soporte, CuotasBaile $factura): ?string
   {
      if (!$soporte) return null;
      $nameFile = Str::slug($factura->descripcion) . '_' . time() . '.' . $soporte->getClientOriginalExtension();
      $path = $soporte->storeAs('soportes', $nameFile, 'public');
      return Storage::url($path);
   }

   private function getDeudasPendientesSinLock(int $userId)
   {
      return CuotasBaile::query()->where('user_id', $userId)->where('estado', false)
         ->withSum('pagos as total_pagos', 'monto')
         ->orderBy('descripcion', 'asc')
         ->get();
   }
}
