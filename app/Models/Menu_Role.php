<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu_Role extends Model
{
    use HasFactory;

    protected $table = 'menu_role';

    protected $fillable = [
        'menu_id',
        'role_id',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'role_id');
    }

}
