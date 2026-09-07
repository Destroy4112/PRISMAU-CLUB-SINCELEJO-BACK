<?php

namespace App\Services;

use App\Models\Rubros;
use Illuminate\Support\Facades\DB;

class FacturacionService
{

   public function __construct(
      protected MensualidadService $mensualidadService,
      protected CuotaBaileService $cuotaBaileService
   ) {}

   public function generate(array $data): array
   {
      $rubro = Rubros::findOrFail($data['rubro_id']);

      return DB::transaction(function () use ($data, $rubro) {

         return DB::transaction(function () use ($data, $rubro) {
            if ($data['isCuota']) {
               return $this->cuotaBaileService->generateBillying([
                  'anio' => (int) $data['anio'],
                  'cuotas' => (int) $data['cuotas'],
                  'valor' => $rubro->valor,
                  'rubro_id' => $rubro->id,
               ]);
            }

            return $this->mensualidadService->generateBillying([
               'anio' => (int) $data['anio'],
               'valor' => $rubro->valor,
               'rubro_id' => $rubro->id,
            ]);
         });
      });
   }

   public function updateBillyingsValue(array $data): array
   {
      if ($data['field'] == 'mensualidad') {
         return $this->mensualidadService->updateValueSocio($data['documento'], $data['value']);
      }
      return $this->cuotaBaileService->updateValueSocio($data['documento'], $data['value']);
   }
}
