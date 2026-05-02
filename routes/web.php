<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\UserController;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth', 'tenant'])->group(function () {
    // Tenant-scoped user management
    Route::resource('users', UserController::class)->except(['show']);
});




require __DIR__.'/auth.php';
require __DIR__.'/super-admin.php';
require __DIR__.'/soc.php';
