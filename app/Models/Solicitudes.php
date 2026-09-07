<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitudes extends Model
{
    use HasFactory;

    protected $fillable = [
        'Tipo',
        'Descripcion',
        'user_id',
        'Estado',
        'Respuesta',
    ];

    protected $casts = [
        'Estado' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
