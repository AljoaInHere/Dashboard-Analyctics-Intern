<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Masukkan data awal ke tabel users
        DB::table('users')->insert([
            'name' => 'Andre Luhut',
            'email' => 'andre@test.com',
            'password' => Hash::make('password123'),
        ]);
    }
}