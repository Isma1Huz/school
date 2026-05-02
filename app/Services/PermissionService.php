<?php

namespace App\Services;

use App\Models\Module;
use App\Models\Tenant;
use App\Models\User;

class PermissionService
{
    /**
     * Sync all default permissions for a module to a tenant's users' roles.
     * Requires spatie/laravel-permission to be configured.
     */
    public function syncModulePermissions(Module $module): void
    {
        foreach ($module->default_permissions as $permissionName) {
            \Spatie\Permission\Models\Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web']
            );
        }
    }

    /**
     * Grant a user a specific module permission.
     */
    public function grantPermission(User $user, string $permission): void
    {
        if (!method_exists($user, 'givePermissionTo')) {
            return;
        }

        $user->givePermissionTo($permission);
    }

    /**
     * Revoke a user's specific module permission.
     */
    public function revokePermission(User $user, string $permission): void
    {
        if (!method_exists($user, 'revokePermissionTo')) {
            return;
        }

        $user->revokePermissionTo($permission);
    }

    /**
     * Return all permissions grouped by module key.
     * e.g. ['inventory' => ['inventory:view_products', ...], ...]
     */
    public function getPermissionsByModule(): array
    {
        if (!class_exists(\Spatie\Permission\Models\Permission::class)) {
            return [];
        }

        return \Spatie\Permission\Models\Permission::all()
            ->filter(fn ($p) => str_contains($p->name, ':'))
            ->groupBy(fn ($p) => explode(':', $p->name)[0])
            ->map(fn ($group) => $group->pluck('name')->toArray())
            ->toArray();
    }

    /**
     * Check if the current tenant has a module enabled and the user has access to it.
     */
    public function userCanAccessModule(User $user, string $moduleKey): bool
    {
        if (!app()->has('tenant')) {
            return false;
        }

        /** @var Tenant $tenant */
        $tenant = app('tenant');

        if (!$tenant->hasModule($moduleKey)) {
            return false;
        }

        return $user->canAccessModule($moduleKey);
    }
}
