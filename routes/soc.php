<?php

use App\Http\Controllers\Soc\IpBlacklistController;
use App\Http\Controllers\Soc\SocController;
use Illuminate\Support\Facades\Route;

Route::prefix('soc')->name('soc.')->middleware(['auth', 'soc'])->group(function () {
    Route::get('/', [SocController::class, 'dashboard'])->name('dashboard');
    Route::get('activity-logs', [SocController::class, 'activityLogs'])->name('activity-logs');
    Route::get('rate-limit-logs', [SocController::class, 'rateLimitLogs'])->name('rate-limit-logs');
    Route::get('ip-management', [SocController::class, 'ipManagement'])->name('ip-management');

    Route::post('ip-blacklist', [IpBlacklistController::class, 'store'])->name('ip-blacklist.store');
    Route::delete('ip-blacklist/{ipBlacklist}', [IpBlacklistController::class, 'destroy'])->name('ip-blacklist.destroy');
});
