<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Insumo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'unidad',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function presentaciones(): HasMany
    {
        return $this->hasMany(InsumoPresentacion::class, 'insumo_id');
    }

}
