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
        ->assertViewHas('dataOperator')
        ->assertViewHas('records');
});

test('dashboard analytics filters by preset and operator', function () {
    $role = Role::firstOrCreate(['name' => 'eksekutif', 'guard_name' => 'web']);
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole($role);

    SmsTraffic::factory()->create([
        'tanggal' => now()->toDateString(),
        'operator' => 'Telkomsel',
        'total_dikirim' => 1000,
        'total_sukses' => 950,
        'total_gagal' => 50,
    ]);

    SmsTraffic::factory()->create([
        'tanggal' => now()->subDays(10)->toDateString(),
        'operator' => 'Indosat',
        'total_dikirim' => 500,
        'total_sukses' => 450,
        'total_gagal' => 50,
    ]);

    Livewire::actingAs($user)
        ->test(DashboardAnalytics::class)
        ->call('setPreset', 'today')
        ->assertViewHas('totalBulanIni', 1000)
        ->set('operator', 'Indosat')
        ->assertViewHas('totalBulanIni', 0)
        ->call('setPreset', '30_days')
        ->assertViewHas('totalBulanIni', 500)
        ->call('sort', 'total_dikirim')
        ->assertSet('sortBy', 'total_dikirim')
        ->call('setSort', 'tanggal', 'asc')
        ->assertSet('sortBy', 'tanggal')
        ->assertSet('sortDirection', 'asc');
});

test('pengguna umum cannot perform write or export actions', function () {
    $role = Role::firstOrCreate(['name' => 'pengguna-umum', 'guard_name' => 'web']);
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole($role);

    Livewire::actingAs($user)
        ->test(DashboardAnalytics::class)
        ->call('openInputModal')
        ->assertForbidden();

    Livewire::actingAs($user)
        ->test(DashboardAnalytics::class)
        ->call('exportCsv')
        ->assertForbidden();
});

test('eksekutif can save traffic with validation and duplicate overwrite confirmation', function () {
    $role = Role::firstOrCreate(['name' => 'eksekutif', 'guard_name' => 'web']);
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole($role);

    $today = now()->toDateString();

    // Mismatch arithmetic should fail validation
    Livewire::actingAs($user)
        ->test(DashboardAnalytics::class)
        ->set('inputTanggal', $today)
        ->set('inputOperator', 'Telkomsel')
        ->set('inputTotalDikirim', 100)
        ->set('inputTotalSukses', 80)
        ->set('inputTotalGagal', 10)
        ->call('saveTraffic')
        ->assertHasErrors(['inputTotalDikirim']);

    // Valid save
    Livewire::actingAs($user)
        ->test(DashboardAnalytics::class)
        ->set('inputTanggal', $today)
        ->set('inputOperator', 'Telkomsel')
        ->set('inputTotalDikirim', 100)
        ->set('inputTotalSukses', 90)
        ->set('inputTotalGagal', 10)
        ->call('saveTraffic')
        ->assertHasNoErrors()
        ->assertSet('showInputModal', false);

    expect(SmsTraffic::whereDate('tanggal', $today)->where('operator', 'Telkomsel')->first()?->total_dikirim)->toBe(100);

    // Duplicate triggers confirmation, then forceOverwrite updates it
    Livewire::actingAs($user)
        ->test(DashboardAnalytics::class)
        ->set('inputTanggal', $today)
        ->set('inputOperator', 'Telkomsel')
        ->set('inputTotalDikirim', 200)
        ->set('inputTotalSukses', 190)
        ->set('inputTotalGagal', 10)
        ->call('saveTraffic')
        ->assertSet('confirmOverwrite', true)
        ->call('saveTraffic', true)
        ->assertSet('showInputModal', false);

    expect(SmsTraffic::whereDate('tanggal', $today)->where('operator', 'Telkomsel')->first()?->total_dikirim)->toBe(200);
});

test('dashboard analytics falls back to all available records when current month is empty', function () {
    $role = Role::firstOrCreate(['name' => 'eksekutif', 'guard_name' => 'web']);
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole($role);

    SmsTraffic::factory()->create([
        'tanggal' => now()->subMonths(2)->toDateString(),
        'operator' => 'Telkomsel',
        'total_dikirim' => 2500,
        'total_sukses' => 2400,
        'total_gagal' => 100,
    ]);

    Livewire::actingAs($user)
        ->test(DashboardAnalytics::class)
        ->assertSet('preset', 'all')
        ->assertViewHas('totalBulanIni', 2500)
        ->call('setPreset', 'today')
        ->assertViewHas('totalBulanIni', 0)
        ->call('resetFilters')
        ->assertSet('preset', 'all')
        ->assertSet('operator', 'all')
        ->assertViewHas('totalBulanIni', 2500);
});

test('dashboard analytics supports clicking date and operator to filter and toggle back', function () {
    $role = Role::firstOrCreate(['name' => 'eksekutif', 'guard_name' => 'web']);
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole($role);

    $dateA = now()->toDateString();
    $dateB = now()->subDay()->toDateString();

    SmsTraffic::factory()->create([
        'tanggal' => $dateA,
        'operator' => 'Telkomsel',
        'total_dikirim' => 1200,
        'total_sukses' => 1150,
        'total_gagal' => 50,
    ]);

    SmsTraffic::factory()->create([
        'tanggal' => $dateB,
        'operator' => 'Indosat',
        'total_dikirim' => 800,
        'total_sukses' => 760,
        'total_gagal' => 40,
    ]);

    Livewire::actingAs($user)
        ->test(DashboardAnalytics::class)
        ->call('selectOperator', 'Telkomsel')
        ->assertSet('operator', 'Telkomsel')
        ->assertViewHas('totalBulanIni', 1200)
        ->call('selectOperator', 'Telkomsel')
        ->assertSet('operator', 'all')
        ->call('selectDate', $dateB)
        ->assertSet('startDate', $dateB)
        ->assertSet('endDate', $dateB)
        ->assertSet('preset', 'custom')
        ->assertViewHas('totalBulanIni', 800)
        ->call('selectDate', $dateB)
        ->assertSet('preset', 'all');
});

test('dashboard analytics shifts period forward and backward by current day span', function () {
    $role = Role::firstOrCreate(['name' => 'eksekutif', 'guard_name' => 'web']);
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole($role);

    $today = now()->toDateString();
    $yesterday = now()->subDay()->toDateString();
    $tomorrow = now()->addDay()->toDateString();

    Livewire::actingAs($user)
        ->test(DashboardAnalytics::class)
        ->call('setPreset', 'today')
        ->assertSet('startDate', $today)
        ->assertSet('endDate', $today)
        ->call('shiftPeriod', -1)
        ->assertSet('startDate', $yesterday)
        ->assertSet('endDate', $yesterday)
        ->assertSet('preset', 'custom')
        ->assertDispatched('period-slid')
        ->call('shiftPeriod', 1)
        ->assertSet('startDate', $today)
        ->assertSet('endDate', $today)
        ->call('shiftPeriod', 1)
        ->assertSet('startDate', $tomorrow)
        ->assertSet('endDate', $tomorrow);
});
