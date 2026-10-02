<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsumoPresentacion extends Model
{
    use HasFactory;

    protected $table = 'insumo_presentaciones';

    protected $fillable = [
        'insumo_id',
        'nombre',
        'stock',
        'activo',
    ];

    protected $casts = [
        'cantidad_base' => 'decimal:3',
        'activo' => 'boolean',
    ];

    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class, 'insumo_id');
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'insumo_presentacion_id');
    }
}
