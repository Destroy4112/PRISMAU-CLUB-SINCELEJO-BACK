<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = DB::table('users')->insertGetId([
            'Documento' => 'Adminprismau',
            'password' => Hash::make('PrismaU.123'),
            'Rol' => 0
        ]);

        DB::table('admins')->insert([
            'user_id' => $userId,
            "Nombre" => "SuperAdmin",
            "Apellidos" => "PrismaU",
            "Correo" => "superadmin@prismau.com",
            "Telefono" => "0000000000",
            "Estado" => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
