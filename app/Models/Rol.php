<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = ['Rol'];

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'menu_role', 'role_id', 'menu_id');
    }
}
