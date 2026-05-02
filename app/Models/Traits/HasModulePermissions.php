<?php

namespace App\Models\Traits;

trait HasModulePermissions
{
    /**
     * Check whether the user has a given module permission.
     * Falls back to Spatie's standard hasPermissionTo() if available.
     */
    public function hasModulePermission(string $permission): bool
    {
        if (method_exists($this, 'hasPermissionTo')) {
            return $this->hasPermissionTo($permission);
        }

        return false;
    }

    /**
     * Return all module permissions that belong to a specific module key.
     * e.g. getModulePermissions('inventory') → ['inventory:view_products', ...]
     */
    public function getModulePermissions(string $moduleKey): array
    {
        if (!method_exists($this, 'getAllPermissions')) {
            return [];
        }

        return $this->getAllPermissions()
            ->filter(fn ($p) => str_starts_with($p->name, $moduleKey . ':'))
            ->pluck('name')
            ->toArray();
    }

    /**
     * Check whether the user has access to any permission within the given module.
     */
    public function canAccessModule(string $moduleKey): bool
    {
        if (!method_exists($this, 'getAllPermissions')) {
            return false;
        }

        return $this->getAllPermissions()
            ->contains(fn ($p) => str_starts_with($p->name, $moduleKey . ':'));
    }
}
