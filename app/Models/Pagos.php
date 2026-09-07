<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pagos extends Model
{
    use HasFactory;

    protected $fillable = [
        'mensualidad_id',
        'email',
        'nombre',
        'identificacion',
        'metodo_pago',
        'referencia_pago',
        'monto',
        'tarjeta',
        'fecha_pago',
        'soporte',
    ];

    public function mensualidad()
    {
        return $this->belongsTo(Mensualidades::class);
    }
}
