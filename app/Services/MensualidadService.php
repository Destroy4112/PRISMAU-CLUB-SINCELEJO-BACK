<?php

namespace App\Services;

use App\Models\Mensualidades;
use App\Models\Pagos;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MensualidadService
{

   public function __construct(
      protected UserService $userService
   ) {}

   public function generateBillying(array $data): array
   {
      $anio = $data['anio'];
      $valor = $data['valor'];

      $usuarios = User::query()->whereIn('Rol', [2, 3])->pluck('id');

      if ($usuarios->isEmpty()) {
         return [
            'status' => false,
            'message' => 'No se encontraron usuarios para generar mensualidades.',
         ];
      }

      $mensualidadesExistentes = Mensualidades::query()
         ->whereIn('user_id', $usuarios)
         ->whereYear('fecha', $anio)
         ->get(['user_id', 'fecha'])
         ->groupBy('user_id')
         ->map(
            fn($mensualidades) => $mensualidades
               ->map(
                  fn($mensualidad) => Carbon::parse(
                     $mensualidad->fecha
                  )->month
               )
               ->unique()
               ->values()
               ->all()
         );

      $creadas = 0;

      foreach ($usuarios as $userId) {
         $mesesExistentes = $mensualidadesExistentes->get(
            $userId,
            []
         );

         for ($mes = 1; $mes <= 12; $mes++) {
            if (in_array($mes, $mesesExistentes, true)) {
               continue;
            }

            Mensualidades::create([
               'user_id' => $userId,
               'fecha' => Carbon::create($anio, $mes, 1),
               'valor' => $valor,
               'estado' => false,
            ]);

            $creadas++;
         }
      }

      if ($creadas === 0) {
         return [
            'status' => false,
            'message' => "Todos los usuarios ya tienen las 12 mensualidades del año {$anio}.",
            'creadas' => 0,
         ];
      }

      return [
         'status' => true,
         'message' => "Mensualidades generadas correctamente.",
      ];
   }

   public function createPreference(array $data): array
   {
      $factura = Mensualidades::query()
         ->where('id', $data['mensualidad_id'])
         ->withSum('pagos as total_pagos', 'monto')
         ->firstOrFail();

      $aplicarDesdeMasAntigua = (bool) ($data['aplicar_desde_mas_antigua'] ?? false);

      $deudas = $this->getDeudasPendientesSinLock($factura->user_id);

      if ($deudas->isEmpty()) {
         return [
            'status' => false,
            'message' => 'El usuario no tiene mensualidades pendientes.',
            'errors' => ['El usuario no tiene mensualidades pendientes.'],
         ];
      }

      if ($aplicarDesdeMasAntigua) {
         $mensualidadBase = $deudas->first();
         $valorNetoDeseado = isset($data['monto']) && $data['monto'] !== null && $data['monto'] !== ''
            ? (float) $data['monto']
            : $this->calcularSaldoMensualidad($mensualidadBase);
      } else {
         if ($factura->estado) {
            return [
               'status' => false,
               'message' => 'La mensualidad seleccionada ya se encuentra pagada.',
               'errors' => ['La mensualidad seleccionada ya se encuentra pagada.'],
            ];
         }

         $mensualidadBase = $factura;
         $valorNetoDeseado = $this->calcularSaldoMensualidad($factura);
      }

      $saldoBase = $this->calcularSaldoMensualidad($mensualidadBase);

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
            'message' => "El valor ingresado no cubre el saldo de la mensualidad: {$saldoBase}.",
            'errors' => ["El valor ingresado no cubre el saldo de la mensualidad: {$saldoBase}."],
         ];
      }

      if (!$aplicarDesdeMasAntigua && $valorNetoDeseado > $saldoBase) {
         return [
            'status' => false,
            'message' => 'El valor ingresado supera el saldo de la mensualidad seleccionada. Para pagar un valor diferente debe activar la opción correspondiente.',
            'errors' => ['El valor ingresado supera el saldo de la mensualidad seleccionada. Para pagar un valor diferente debe activar la opción correspondiente.'],
         ];
      }

      $totalDeuda = $deudas->sum(fn($m) => $this->calcularSaldoMensualidad($m));

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
            'mensualidad_id' => $factura->id,
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

   public function webhookMensualidades(string $paymentId, array $respuesta): array
   {
      if (($respuesta['status'] ?? '') !== 'approved') {
         return [
            'status' => true,
            'message' => 'Pago no aprobado todavía',
         ];
      }

      $externalReference = $respuesta['external_reference'] ?? null;

      if (!$externalReference) {
         return [
            'status' => false,
            'message' => 'Referencia externa faltante',
         ];
      }

      $factura = Mensualidades::query()
         ->where('id', $externalReference)
         ->first();

      if (!$factura) {
         return [
            'status' => false,
            'message' => 'Mensualidad no encontrada',
         ];
      }

      if (Pagos::query()->where('referencia_pago', $paymentId)->exists()) {
         return [
            'status' => true,
            'message' => 'Pago ya procesado',
         ];
      }

      $metadata = $respuesta['metadata'] ?? [];

      $montoAplicar = isset($metadata['neto_deseado'])
         ? (float) $metadata['neto_deseado']
         : 0;

      if ($montoAplicar <= 0) {
         return [
            'status' => false,
            'message' => 'No se encontró el valor neto a aplicar en el pago.',
            'code' => 400,
         ];
      }

      $aplicarDesdeMasAntigua = filter_var(
         $metadata['aplicar_desde_mas_antigua'] ?? true,
         FILTER_VALIDATE_BOOLEAN
      );

      $fechaPago = isset($respuesta['date_created'])
         ? Carbon::parse($respuesta['date_created'])->format('Y-m-d H:i:s')
         : now();

      return $this->aplicarPagoMensualidades([
         'mensualidad_id' => $factura->id,
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

      $mensualidadesPendientes = $socio->mensualidades()->where('estado', false);

      if (!$mensualidadesPendientes->exists()) {
         return [
            'status' => false,
            'message' => 'No hay mensualidades pendientes para actualizar.',
         ];
      }

      $actualizadas = $mensualidadesPendientes->update(['valor' => $valor,]);

      return [
         'status' => true,
         'message' => "Mensualidades actualizadas correctamente.",
         'actualizadas' => $actualizadas,
      ];
   }

   public function aplicarPagoMensualidades(array $data): array
   {
      return DB::transaction(function () use ($data) {

         $factura = Mensualidades::query()
            ->where('id', $data['mensualidad_id'])
            ->withSum('pagos as total_pagos', 'monto')
            ->lockForUpdate()
            ->firstOrFail();

         $userId = $factura->user_id;

         $aplicarDesdeMasAntigua = (bool) ($data['aplicar_desde_mas_antigua'] ?? false);

         if (!$aplicarDesdeMasAntigua && $factura->estado) {
            return [
               'status' => false,
               'message' => 'La mensualidad seleccionada ya se encuentra pagada.',
            ];
         }

         $deudas = $this->getDeudasPendientesParaPago($userId);

         if ($deudas->isEmpty()) {
            return [
               'status' => false,
               'message' => 'El usuario no tiene mensualidades pendientes.',
            ];
         }

         if ($aplicarDesdeMasAntigua) {
            $mensualidadesAProcesar = $deudas;
            $mensualidadBase = $deudas->first();
         } else {
            $mensualidadesAProcesar = collect([$factura]);
            $mensualidadBase = $factura;
         }

         $saldoBase = $this->calcularSaldoMensualidad($mensualidadBase);

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
               'message' => "El valor ingresado no cubre el saldo de la mensualidad: {$saldoBase}.",
            ];
         }

         if (!$aplicarDesdeMasAntigua && $montoPagado > $saldoBase) {
            return [
               'status' => false,
               'message' => 'El valor ingresado supera el saldo de la mensualidad seleccionada. Para pagar un valor diferente debe activar la opción correspondiente.',
            ];
         }

         $totalDeuda = $deudas->sum(function ($mensualidad) {
            return $this->calcularSaldoMensualidad($mensualidad);
         });

         if ($aplicarDesdeMasAntigua && $montoPagado > $totalDeuda) {
            return [
               'status' => false,
               'message' => "El monto ingresado excede el total de la deuda pendiente del usuario: {$totalDeuda}.",
            ];
         }

         $montoRestante = $montoPagado;
         $pagosAplicados = [];

         foreach ($mensualidadesAProcesar as $mensualidad) {
            if ($montoRestante <= 0) {
               break;
            }

            $saldoMensualidad = $this->calcularSaldoMensualidad($mensualidad);

            if ($saldoMensualidad <= 0) {
               $mensualidad->update(['estado' => true]);
               continue;
            }

            $valorAplicado = min($montoRestante, $saldoMensualidad);

            Pagos::create([
               'mensualidad_id' => $mensualidad->id,
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

            $nuevoSaldo = $saldoMensualidad - $valorAplicado;

            if ($nuevoSaldo <= 0) {
               $mensualidad->update(['estado' => true]);
               $mensualidadId = $mensualidad->id;

               DB::afterCommit(function () use ($userId, $mensualidadId) {
                  $this->userService->confirmarPagoMensualidad($userId, $mensualidadId, 'Aprobado');
               });
            }

            $pagosAplicados[] = [
               'mensualidad_id' => $mensualidad->id,
               'fecha' => $mensualidad->fecha,
               'saldo_anterior' => $saldoMensualidad,
               'valor_aplicado' => $valorAplicado,
               'saldo_nuevo' => max($nuevoSaldo, 0),
               'pagada' => $nuevoSaldo <= 0,
            ];
         }

         return [
            'status' => true,
            'message' => 'Pago procesado correctamente',
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

   public function getByDocumento(array $filters, string $documento): array
   {
      $limit = min(max((int) ($filters['limit'] ?? 30), 1), 100);
      $search = isset($filters['search']) && $filters['search'] !== '' ? (int) $filters['search'] : null;
      $state = $filters['state'] ?? null;

      if ($state !== null && $state !== '') {
         $state = filter_var($state, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
      }

      $user = User::where('Documento', $documento)->firstOrFail();

      $baseQuery = $user->mensualidades();

      $stats = [
         'total' => (clone $baseQuery)->count(),
         'pagadas' => (clone $baseQuery)->where('estado', true)->count(),
         'pendientes' => (clone $baseQuery)->where('estado', false)->count(),
      ];

      $mensualidades = $user->mensualidades()->with('pagos')
         ->when($search !== null, function ($query) use ($search) {
            $query->whereYear('fecha', $search);
         })
         ->when($state !== null, function ($query) use ($state) {
            $query->where('estado', $state);
         })
         ->orderBy('fecha', 'desc')
         ->paginate($limit);

      return [
         'data' => $mensualidades->items(),
         'total' => $mensualidades->total(),
         'page' => $mensualidades->currentPage(),
         'limit' => $mensualidades->perPage(),
         'totalPages' => $mensualidades->lastPage(),
         'stats' => $stats,
      ];
   }

   public function getFactura(int $id): Mensualidades
   {
      return Mensualidades::findOrFail($id);
   }

   public function getResumePaymentReference(string $referenciaPago): array
   {
      $pagos = Pagos::query()
         ->with(['mensualidad' => function ($query) {
            $query->with('pagos');
         }])
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
         $mensualidad = $pago->mensualidad;

         $valorAplicado = (float) $pago->monto;
         $saldoNuevo = $mensualidad ? (float) $mensualidad->restante : 0;

         $saldoAnterior = $saldoNuevo + $valorAplicado;

         return [
            'mensualidad_id' => $mensualidad?->id,
            'fecha' => $mensualidad?->fecha,
            'saldo_anterior' => $saldoAnterior,
            'valor_aplicado' => $valorAplicado,
            'saldo_nuevo' => $saldoNuevo,
            'pagada' => (bool) ($mensualidad?->estado ?? false),
         ];
      })->values()->all();

      return [
         'status' => true,
         'message' => 'Resumen del pago obtenido correctamente',
         'data' => [
            'user_id' => $pagos->first()?->mensualidad?->user_id,
            'monto_pagado' => (float) $pagos->sum('monto'),
            'monto_restante' => 0,
            'aplicar_desde_mas_antigua' => count($pagosAplicados) > 1,
            'pagos_aplicados' => $pagosAplicados,
         ],
      ];
   }

   //------------------------ Helpers --------------------------------------

   private function getDeudasPendientesParaPago(int $userId)
   {
      return Mensualidades::query()
         ->where('user_id', $userId)
         ->where('estado', false)
         ->withSum('pagos as total_pagos', 'monto')
         ->orderBy('fecha', 'asc')
         ->lockForUpdate()
         ->get();
   }

   private function calcularSaldoMensualidad(Mensualidades $mensualidad): float
   {
      return max((float) $mensualidad->valor - (float) ($mensualidad->total_pagos ?? 0), 0);
   }

   public function guardarSoporte(?UploadedFile $soporte, Mensualidades $factura): ?string
   {
      if (!$soporte) return null;
      $nameFile = Str::slug($factura->fecha) . '_' . time() . '.' . $soporte->getClientOriginalExtension();
      $path = $soporte->storeAs('soportes', $nameFile, 'public');
      return Storage::url($path);
   }

   private function getDeudasPendientesSinLock(int $userId)
   {
      return Mensualidades::query()
         ->where('user_id', $userId)
         ->where('estado', false)
         ->withSum('pagos as total_pagos', 'monto')
         ->orderBy('fecha', 'asc')
         ->get();
   }
}
