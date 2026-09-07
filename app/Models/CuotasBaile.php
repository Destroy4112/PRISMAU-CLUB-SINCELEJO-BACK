<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuotasBaile extends Model
{
   use HasFactory;

   protected $fillable = [
      'user_id',
      'descripcion',
      'valor',
      'estado',
   ];

   protected $casts = [
      'valor' => 'decimal:2',
      'estado' => 'boolean',
   ];

   protected $appends = ['total_pagos', 'restante'];

   public function pagos()
   {
      return $this->hasMany(PagosCuotasBaile::class);
   }

   public function user()
   {
      return $this->belongsTo(User::class);
   }

   public function getTotalPagosAttribute()
   {
      if (array_key_exists('pagos_sum_monto', $this->attributes)) {
         return (float) $this->attributes['pagos_sum_monto'];
      }

      if ($this->relationLoaded('pagos')) {
         return $this->pagos->sum('monto');
      }

      return (float) $this->pagos()->sum('monto');
   }

   public function getRestanteAttribute()
   {
      return max((float) $this->valor - (float) $this->total_pagos, 0);
   }
}
