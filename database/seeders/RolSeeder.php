<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                "Rol" => "Admin",
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                "Rol" => "Asociado",
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                "Rol" => "Adherente",
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                "Rol" => "Empleado",
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                "Rol" => "Familiar",
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                "Rol" => "Portero",
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                "Rol" => "SuperAdmin",
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        foreach ($roles as $rol) {
            Rol::create($rol);
        }
    }
}
