<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estados extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'Estado',
        'Motivo',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
