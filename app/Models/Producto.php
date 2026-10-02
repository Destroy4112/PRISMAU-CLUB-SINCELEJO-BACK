<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'imagen',
        'tipo',
        'categoria',
        'precio',
        'cocina_id',
        'insumo_presentacion_id',
        'cantidad_por_plato',
        'activo',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function cocina(): BelongsTo
    {
        return $this->belongsTo(Cocina::class, 'cocina_id');
    }

    public function insumoPresentacion(): BelongsTo
    {
        return $this->belongsTo(InsumoPresentacion::class, 'insumo_presentacion_id');
    }
}
