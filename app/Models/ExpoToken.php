<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpoToken extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'token', 'enabled', 'last_used_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
