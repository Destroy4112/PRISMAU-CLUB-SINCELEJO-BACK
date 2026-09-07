<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagosCuotasBaile extends Model
{
    use HasFactory;

    protected $fillable = [
        'cuotas_baile_id',
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

    public function cuota()
    {
        return $this->belongsTo(CuotasBaile::class, 'cuotas_baile_id');
    }
}
