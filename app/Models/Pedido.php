<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mesa_id',
        'estado',
        'total',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class);
    }

    public function pedidoDetalle(): HasMany
    {
        return $this->hasMany(PedidoDetalle::class, 'pedido_id');
    }

    public function recalcularTotal(): void
    {
        $this->total = (int) $this->pedidoDetalle()->whereNotIn('estado', ['Cancelado', 'Rechazado'])->sum('subtotal');
        $this->save();
    }

    public function isAbierto(): bool
    {
        return $this->estado === 'Abierto';
    }
}
