<?php

namespace App\Services;

use App\Models\Module;
use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ModuleService
{
    private const CACHE_TTL = 300;

    /**
     * Return all modules that are enabled for the given tenant.
     */
    public function getEnabledModulesForTenant(Tenant $tenant): \Illuminate\Support\Collection
    {
        return Cache::remember(
            "tenant_{$tenant->id}_enabled_modules",
            self::CACHE_TTL,
            fn () => $tenant->enabledModules()->active()->orderBy('sort_order')->get()
        );
    }

    /**
     * Check whether all dependencies of a module are enabled for the tenant.
     */
    public function canEnableModule(Module $module, Tenant $tenant): bool
    {
        foreach ($module->dependencies ?? [] as $depKey) {
            $dep = Module::where('key', $depKey)->first();

            if (!$dep) {
                return false;
            }

            $enabled = $tenant->modules()
                ->where('modules.id', $dep->id)
                ->wherePivot('is_enabled', true)
                ->exists();

            if (!$enabled) {
                return false;
            }
        }

        return true;
    }

    /**
     * Enable a module for a tenant (with optional settings).
     * Dependencies are enabled automatically in a transaction.
     */
    public function enableModule(Tenant $tenant, Module $module, array $settings = []): void
    {
        if (!$module->isAvailableForTenantType($tenant->type)) {
            throw new \RuntimeException(
                "Module [{$module->key}] is not available for tenant type [{$tenant->type}]."
            );
        }

        DB::transaction(function () use ($tenant, $module, $settings) {
            // Recursively enable all dependencies first
            foreach ($module->dependencies ?? [] as $depKey) {
                $dep = Module::where('key', $depKey)->first();
                if ($dep && !$this->isModuleEnabled($tenant, $depKey)) {
                    $this->enableModule($tenant, $dep);
                }
            }

            $tenant->modules()->syncWithoutDetaching([
                $module->id => [
                    'is_enabled' => true,
                    'settings'   => $settings,
                ],
            ]);
        });

        $this->clearModuleCache($tenant);
    }

    /**
     * Check if a specific module key is currently enabled for a tenant.
     */
    public function isModuleEnabled(Tenant $tenant, string $moduleKey): bool
    {
        return $tenant->modules()
            ->where('key', $moduleKey)
            ->wherePivot('is_enabled', true)
            ->exists();
    }

    /**
     * Disable a module for a tenant.
     * Throws if another enabled module depends on this one.
     */
    public function disableModule(Tenant $tenant, Module $module): void
    {
        // Check that no other enabled module depends on this one
        $dependents = $tenant->enabledModules()
            ->get()
            ->filter(fn (Module $m) => in_array($module->key, $m->dependencies ?? []));

        if ($dependents->isNotEmpty()) {
            $keys = $dependents->pluck('key')->implode(', ');
            throw new \RuntimeException(
                "Cannot disable [{$module->key}]: it is required by [{$keys}]."
            );
        }

        $tenant->modules()->updateExistingPivot($module->id, ['is_enabled' => false]);
        $this->clearModuleCache($tenant);
    }

    /**
     * Ensure all core modules are enabled for a tenant.
     */
    public function enableCoreModulesForTenant(Tenant $tenant): void
    {
        $coreModules = Module::where('is_core', true)->active()->get();

        foreach ($coreModules as $module) {
            if (!$tenant->modules()->where('modules.id', $module->id)->exists()) {
                $tenant->modules()->attach($module->id, ['is_enabled' => true]);
            } else {
                $tenant->modules()->updateExistingPivot($module->id, ['is_enabled' => true]);
            }
        }

        $this->clearModuleCache($tenant);
    }

    /**
     * Register or update a module from its module.json manifest.
     * Also seeds Spatie permission rows for every permission in the manifest.
     */
    public function registerFromManifest(string $moduleName): Module
    {
        $manifestPath = app_path("Modules/{$moduleName}/module.json");

        if (!File::exists($manifestPath)) {
            throw new \RuntimeException("module.json not found for module [{$moduleName}].");
        }

        $manifest = json_decode(File::get($manifestPath), true);

        $module = Module::updateOrCreate(
            ['key' => $manifest['key']],
            [
                'name'                  => $manifest['name'],
                'icon'                  => $manifest['icon'] ?? null,
                'description'           => $manifest['description'] ?? null,
                'allowed_tenant_types'  => $manifest['allowed_tenant_types'] ?? ['school'],
                'dependencies'          => $manifest['dependencies'] ?? [],
                'default_permissions'   => $manifest['permissions'] ?? [],
                'settings_schema'       => $manifest['settings_schema'] ?? [],
                'sort_order'            => $manifest['sort_order'] ?? 100,
                'is_core'               => $manifest['is_core'] ?? false,
                'is_active'             => true,
                'is_globally_disabled'  => false,
            ]
        );

        // Seed Spatie permission rows for every permission declared in the manifest
        app(PermissionService::class)->syncModulePermissions($module);

        return $module;
    }

    /**
     * Check if a module is enabled for the current tenant (bound in the container).
     */
    public function isModuleEnabledForCurrentTenant(string $moduleKey): bool
    {
        if (!app()->has('tenant')) {
            return false;
        }

        /** @var Tenant $tenant */
        $tenant = app('tenant');

        return $this->getEnabledModulesForTenant($tenant)
            ->contains('key', $moduleKey);
    }

    public function clearModuleCache(Tenant $tenant): void
    {
        Cache::forget("tenant_{$tenant->id}_enabled_modules");
    }
}
