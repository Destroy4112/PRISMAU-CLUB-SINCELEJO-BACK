<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservas extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'espacio_id',
        'Fecha',
        'Inicio',
        'Fin',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
   
    public function espacio()
    {
        return $this->belongsTo(Espacio::class);
    }
}
