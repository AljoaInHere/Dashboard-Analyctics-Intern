<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('users without eksekutif role cannot visit dashboard', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertForbidden();
});

test('verified users with eksekutif role can visit the dashboard', function () {
    $role = Role::firstOrCreate(['name' => 'eksekutif', 'guard_name' => 'web']);

    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    $user->assignRole($role);

    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});