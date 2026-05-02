<?php

declare(strict_types=1);

namespace App\Modules\Core\Providers;

use App\Modules\Core\Console\Commands\HorizonQueueMonitor;
use App\Modules\Core\Http\Middleware\TenantRateLimiter;
use App\Modules\Core\Services\MediaService;
use App\Modules\Core\Services\ModuleSettingsService;
use App\Modules\Core\Services\NotificationService;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register the event service provider
        $this->app->register(CoreEventServiceProvider::class);

        // Register singletons
        $this->app->singleton(NotificationService::class);
        $this->app->singleton(ModuleSettingsService::class);
        $this->app->singleton(MediaService::class);
    }

    public function boot(): void
    {
        // Register the tenant throttle middleware alias
        $this->app['router']->aliasMiddleware('tenant.throttle', TenantRateLimiter::class);

        // Register Artisan commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                HorizonQueueMonitor::class,
            ]);
        }

        // Restrict Horizon dashboard access to super admins only
        if (class_exists(\Laravel\Horizon\Horizon::class)) {
            \Laravel\Horizon\Horizon::auth(function ($request) {
                return $request->user() && $request->user()->user_type === 'super_admin';
            });
        }
    }
}
