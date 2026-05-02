<?php

use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\ModuleController;
use App\Http\Controllers\SuperAdmin\PlanController;
use App\Http\Controllers\SuperAdmin\TenantController;
use App\Http\Controllers\SuperAdmin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('super-admin')->name('super-admin.')->middleware(['auth', 'super_admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('tenants', TenantController::class);
    Route::resource('modules', ModuleController::class)->only(['index', 'update']);
    Route::get('modules/{module}/tenant-overrides', [ModuleController::class, 'tenantOverrides'])
         ->name('modules.tenant-overrides');

    Route::resource('plans', PlanController::class);
    Route::resource('users', UserController::class)->except(['show']);
});
