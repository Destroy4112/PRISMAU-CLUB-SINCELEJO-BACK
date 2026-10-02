<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cocina extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'estado', 'empleado_id'];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'cocina_id');
    }

    public function comidas(): HasMany
    {
        return $this->hasMany(Producto::class, 'cocina_id')
            ->where('tipo', 'COMIDA');
    }
}
