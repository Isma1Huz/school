<?php

namespace App\Helpers;

use App\Services\ModuleService;

class ModuleHelper
{
    /**
     * Check if a module is enabled for the current tenant.
     */
    public static function isEnabled(string $moduleKey): bool
    {
        return app(ModuleService::class)->isModuleEnabledForCurrentTenant($moduleKey);
    }

    /**
     * Return all enabled modules for the current tenant.
     */
    public static function enabledModules(): \Illuminate\Support\Collection
    {
        if (!app()->has('tenant')) {
            return collect();
        }

        return app(ModuleService::class)
            ->getEnabledModulesForTenant(app('tenant'));
    }

    /**
     * Return the path to a module's folder.
     */
    public static function path(string $moduleName): string
    {
        return app_path("Modules/{$moduleName}");
    }

    /**
     * Check if a module folder exists (is registered in the filesystem).
     */
    public static function exists(string $moduleName): bool
    {
        return is_dir(static::path($moduleName));
    }

    /**
     * Return the module manifest data (module.json) for the given module.
     */
    public static function manifest(string $moduleName): array
    {
        $file = static::path($moduleName) . '/module.json';

        if (!file_exists($file)) {
            return [];
        }

        return json_decode(file_get_contents($file), true) ?? [];
    }
}
