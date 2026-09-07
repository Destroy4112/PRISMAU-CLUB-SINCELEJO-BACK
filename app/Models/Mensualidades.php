<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mensualidades extends Model
{
   use HasFactory;

   protected $fillable = [
      'user_id',
      'fecha',
      'valor',
      'estado',
   ];

   protected $casts = [
      'fecha' => 'date',
      'valor' => 'decimal:2',
      'estado' => 'boolean',
   ];

   protected $appends = ['total_pagos', 'restante'];

   public function pagos()
   {
      return $this->hasMany(Pagos::class, 'mensualidad_id');
   }

   public function user()
   {
      return $this->belongsTo(User::class);
   }

   public function getTotalPagosAttribute()
   {
      if ($this->relationLoaded('pagos')) {
         return (float) $this->pagos->sum('monto');
      }

      return (float) $this->pagos()->sum('monto');
   }

   public function getRestanteAttribute()
   {
      return max(0, (float) $this->valor - (float) $this->total_pagos);
   }

   public function estaPagada(): bool
   {
      return $this->total_pagos >= $this->valor;
   }
}
