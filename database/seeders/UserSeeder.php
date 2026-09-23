<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan role 'eksekutif' tersedia
        $roleEksekutif = Role::firstOrCreate([
            'name' => 'eksekutif',
            'guard_name' => 'web',
        ]);

        // Buat atau perbarui akun Eksekutif Andre
        $andre = User::firstOrCreate(
            ['email' => 'andre@test.com'],
            [
                'name' => 'Andre Luhut',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $andre->assignRole($roleEksekutif);

        // Buat atau perbarui akun Admin Eksekutif
        $admin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Administrator Eksekutif',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole($roleEksekutif);
    }
}