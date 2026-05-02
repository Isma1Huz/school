<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withProviders([
        \App\Modules\Core\Providers\CoreServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\IpBlacklistMiddleware::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\IpBlacklistMiddleware::class,
        ]);

        $middleware->alias([
            'tenant'       => \App\Http\Middleware\TenantMiddleware::class,
            'super_admin'  => \App\Http\Middleware\SuperAdminMiddleware::class,
            'soc'          => \App\Http\Middleware\SocMiddleware::class,
            'module'       => \App\Http\Middleware\ModuleAccessMiddleware::class,
            'ip.blacklist' => \App\Http\Middleware\IpBlacklistMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
