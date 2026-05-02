<?php

namespace App\Helpers;

use App\Models\Tenant;

class TenantHelper
{
    /**
     * Return the currently resolved tenant (bound in the IoC container), or null.
     */
    public static function current(): ?Tenant
    {
        return app()->has('tenant') ? app('tenant') : null;
    }

    /**
     * Return the current tenant's ID, or null.
     */
    public static function currentId(): ?int
    {
        return static::current()?->id;
    }

    /**
     * Check whether a tenant context is active.
     */
    public static function hasTenant(): bool
    {
        return app()->has('tenant');
    }

    /**
     * Build the URL for a tenant (using subdomain convention).
     */
    public static function urlFor(Tenant $tenant, string $path = '/'): string
    {
        $base = config('app.url');
        $parsed = parse_url($base);

        $scheme = $parsed['scheme'] ?? 'https';
        $host   = $parsed['host'] ?? 'localhost';
        $port   = isset($parsed['port']) ? ':' . $parsed['port'] : '';

        return "{$scheme}://{$tenant->slug}.{$host}{$port}/" . ltrim($path, '/');
    }

    /**
     * Return the tenant's primary colour with a fallback.
     */
    public static function primaryColor(string $fallback = '#800020'): string
    {
        return static::current()?->primary_color ?? $fallback;
    }

    /**
     * Return the tenant's secondary colour with a fallback.
     */
    public static function secondaryColor(string $fallback = '#FFD700'): string
    {
        return static::current()?->secondary_color ?? $fallback;
    }
}
