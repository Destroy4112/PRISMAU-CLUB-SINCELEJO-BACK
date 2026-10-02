<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ubicacion extends Model
{
    use HasFactory;

    protected $table = 'ubicaciones';

    protected $fillable = ['ubicacion'];

    public function comidas()
    {
        return $this->hasMany(Comida::class);
    }

    public function bebidas()
    {
        return $this->hasMany(Bebida::class);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function mesas()
    {
        return $this->hasMany(Mesa::class);
    }
}
