<?php

use App\Console\Commands\CheckSubscriptionExpiry;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Suspend tenants with expired subscriptions every day at midnight.
Schedule::command(CheckSubscriptionExpiry::class)->daily()->withoutOverlapping();
