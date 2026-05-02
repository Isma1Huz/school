<?php

namespace App\Http\Middleware;

use App\Services\TenantService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function __construct(private readonly TenantService $tenantService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $host   = $request->getHost();
        $tenant = $this->tenantService->resolveFromHost($host);

        if (!$tenant) {
            abort(404, 'Tenant not found.');
        }

        if (!$tenant->isActive()) {
            abort(403, 'This account is inactive.');
        }

        $this->tenantService->bindCurrentTenant($tenant);

        // Make the tenant available to all Inertia pages via shared props
        if (class_exists(\Inertia\Inertia::class)) {
            \Inertia\Inertia::share([
                'tenant' => [
                    'id'                  => $tenant->id,
                    'name'                => $tenant->name,
                    'slug'                => $tenant->slug,
                    'type'                => $tenant->type,
                    'primary_color'       => $tenant->primary_color,
                    'secondary_color'     => $tenant->secondary_color,
                    'logo'                => $tenant->logo,
                    'favicon'             => $tenant->favicon,
                    'subscription_status' => $tenant->subscription_status,
                    'status'              => $tenant->status,
                    'max_users'           => $tenant->max_users,
                    'max_storage_mb'      => $tenant->max_storage_mb,
                ],
            ]);
        }

        return $next($request);
    }
}
