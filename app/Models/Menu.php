<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = ['Name', 'Type', 'Route', 'Icon', 'Color', 'Estado'];

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'menu_role', 'menu_id', 'role_id');
    }
}
