<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;

class TenantService
{
    private const CACHE_TTL = 300; // 5 minutes

    /**
     * Resolve the current tenant from a subdomain or custom domain.
     */
    public function resolveFromHost(string $host): ?Tenant
    {
        return Cache::remember("tenant_host_{$host}", self::CACHE_TTL, function () use ($host) {
            // Check custom domain first
            $tenant = Tenant::where('settings->custom_domain', $host)->first();

            if ($tenant) {
                return $tenant;
            }

            // Fall back to subdomain: e.g. "school1.schoolzee.test" → slug "school1"
            $slug = explode('.', $host)[0];

            return Tenant::where('slug', $slug)->first();
        });
    }

    /**
     * Resolve a tenant by its slug.
     */
    public function resolveFromSlug(string $slug): ?Tenant
    {
        return Cache::remember("tenant_slug_{$slug}", self::CACHE_TTL, function () use ($slug) {
            return Tenant::where('slug', $slug)->first();
        });
    }

    /**
     * Create a new tenant and enable its plan's included modules.
     */
    public function createTenant(array $data): Tenant
    {
        $tenant = Tenant::create($data);

        if ($tenant->subscriptionPlan) {
            $moduleIds = $tenant->subscriptionPlan
                ->includedModules()
                ->active()
                ->pluck('modules.id')
                ->toArray();

            foreach ($moduleIds as $moduleId) {
                $tenant->modules()->attach($moduleId, ['is_enabled' => true]);
            }
        }

        // Always enable core modules
        app(ModuleService::class)->enableCoreModulesForTenant($tenant);

        $this->clearTenantCache($tenant);

        return $tenant->fresh();
    }

    /**
     * Update a tenant and refresh the cache.
     */
    public function updateTenant(Tenant $tenant, array $data): Tenant
    {
        $tenant->update($data);
        $this->clearTenantCache($tenant);

        return $tenant->fresh();
    }

    /**
     * Clear all cached keys related to a tenant.
     */
    public function clearTenantCache(Tenant $tenant): void
    {
        Cache::forget("tenant_slug_{$tenant->slug}");

        // Reconstruct the subdomain host key exactly as resolveFromHost() builds it
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        if ($appHost) {
            Cache::forget("tenant_host_{$tenant->slug}.{$appHost}");
        }

        $customDomain = $tenant->settings['custom_domain'] ?? null;
        if ($customDomain) {
            Cache::forget("tenant_host_{$customDomain}");
        }
    }

    /**
     * Bind the tenant to the container and set it in the current context.
     */
    public function bindCurrentTenant(Tenant $tenant): void
    {
        app()->instance('tenant', $tenant);
    }
}
