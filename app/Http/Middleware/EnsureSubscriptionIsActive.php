<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Block tenant routes when the subscription has expired or been cancelled.
 * Must run after TenantMiddleware (tenant must already be bound).
 */
final class EnsureSubscriptionIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!app()->has('tenant') || app('tenant') === null) {
            return $next($request);
        }

        /** @var \App\Models\Tenant $tenant */
        $tenant = app('tenant');

        if (!$tenant->isSubscriptionValid()) {
            if ($request->header('X-Inertia')) {
                return back()->withErrors([
                    'subscription' => 'Your subscription has expired. Please contact your administrator.',
                ]);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Subscription expired. Please renew your plan.',
                ], 402);
            }

            abort(402, 'Your subscription has expired. Please contact your administrator to renew.');
        }

        return $next($request);
    }
}
