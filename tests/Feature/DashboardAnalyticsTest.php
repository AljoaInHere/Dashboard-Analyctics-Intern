<?php

use App\Livewire\DashboardAnalytics;
use App\Models\SmsTraffic;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

test('dashboard analytics component renders correctly with data contracts', function () {
    $role = Role::firstOrCreate(['name' => 'eksekutif', 'guard_name' => 'web']);
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole($role);

    SmsTraffic::factory()->count(5)->create([
        'tanggal' => now()->format('Y-m-d'),
    ]);

    Livewire::actingAs($user)
        ->test(DashboardAnalytics::class)
        ->assertStatus(200)
        ->assertViewHas('totalBulanIni')
        ->assertViewHas('deliveryRate')
        ->assertViewHas('labelsTren')
        ->assertViewHas('dataTren')
        ->assertViewHas('labelsOperator')
        ->assertViewHas('dataOperator');
});
