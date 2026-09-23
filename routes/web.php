<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Tambahkan 'role:eksekutif' ke dalam array middleware di bawah ini
Route::middleware(['auth', 'verified', 'role:eksekutif'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';