<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Shared test helper for creating and binding tenants.
 *
 * Usage:
 *   use Tests\Concerns\WithTenant;
 *
 *   class MyTest extends TestCase
 *   {
 *       use WithTenant;
 *       ...
 *       [$tenant, $admin] = $this->createTenant();
 *   }
 */
trait WithTenant
{
    /**
     * Create a tenant and an admin user for it, and bind the tenant to the container.
     *
     * @return array{0: Tenant, 1: User}
     */
    protected function createTenant(array $tenantOverrides = [], array $userOverrides = []): array
    {
        $tenant = Tenant::create(array_merge([
            'name'                  => 'Test School',
            'slug'                  => 'test-school-' . uniqid(),
            'type'                  => 'school',
            'status'                => 'active',
            'subscription_status'   => 'active',
            'subscription_end_date' => now()->addYear()->toDateString(),
            'max_users'             => 50,
            'max_storage_mb'        => 1024,
        ], $tenantOverrides));

        $admin = User::withoutGlobalScopes()->create(array_merge([
            'tenant_id'         => $tenant->id,
            'name'              => 'Tenant Admin',
            'email'             => 'admin@test-' . $tenant->id . '.test',
            'password'          => Hash::make('password'),
            'user_type'         => 'tenant_admin',
            'is_active'         => true,
            'email_verified_at' => now(),
        ], $userOverrides));

        $this->bindTenant($tenant);

        return [$tenant, $admin];
    }

    /** Bind (or rebind) a tenant to the container. */
    protected function bindTenant(Tenant $tenant): void
    {
        app()->instance('tenant', $tenant);
    }

    /** Unbind the tenant from the container. */
    protected function clearTenant(): void
    {
        if (app()->has('tenant')) {
            app()->forgetInstance('tenant');
        }
    }

    /** Create a regular staff user for the given tenant. */
    protected function createTenantUser(Tenant $tenant, array $overrides = []): User
    {
        return User::withoutGlobalScopes()->create(array_merge([
            'tenant_id'         => $tenant->id,
            'name'              => 'Staff User',
            'email'             => 'staff-' . uniqid() . '@test.test',
            'password'          => Hash::make('password'),
            'user_type'         => 'staff',
            'is_active'         => true,
            'email_verified_at' => now(),
        ], $overrides));
    }
}
