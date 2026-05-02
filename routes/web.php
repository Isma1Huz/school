<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Core\NotificationController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\RolesController;
use App\Http\Controllers\Tenant\SettingsController;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth', 'tenant', 'subscription.active'])->group(function () {
    // Tenant dashboard
    Route::get('tenant/dashboard', [DashboardController::class, 'index'])->name('tenant.dashboard');

    // Tenant-scoped user management
    Route::resource('users', UserController::class)->except(['show']);

    // Roles management
    Route::resource('roles', RolesController::class)->except(['show']);

    // Settings
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::get('settings/general', [SettingsController::class, 'general'])->name('settings.general');
    Route::put('settings/general', [SettingsController::class, 'updateGeneral'])->name('settings.general.update');
    Route::get('settings/modules', [SettingsController::class, 'modules'])->name('settings.modules');
    Route::patch('settings/modules/toggle', [SettingsController::class, 'toggleModule'])->name('settings.modules.toggle');
    Route::get('settings/permissions', [SettingsController::class, 'permissions'])->name('settings.permissions');
    Route::put('settings/permissions', [SettingsController::class, 'updatePermissions'])->name('settings.permissions.update');
    Route::get('settings/subscription', [SettingsController::class, 'subscription'])->name('settings.subscription');

    // In-app notification bell API (JSON responses)
    Route::get('notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('notifications/{notificationId}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
});




require __DIR__.'/auth.php';
require __DIR__.'/super-admin.php';
require __DIR__.'/soc.php';

