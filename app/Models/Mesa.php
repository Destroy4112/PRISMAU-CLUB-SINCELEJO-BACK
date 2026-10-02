<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero',
        'ubicacion_id',
        'estado',
    ];

    protected $appends = ['disponible'];

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function pedidosActivos()
    {
        return $this->hasMany(Pedido::class)->whereIn('estado', ['Abierto', 'En Preparacion', 'Preparado', 'Servido']);
    }

    public function getDisponibleAttribute()
    {
        return !$this->pedidosActivos()->exists();
    }
}
