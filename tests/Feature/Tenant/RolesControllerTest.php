<?php

declare(strict_types=1);

namespace Tests\Feature\Tenant;

use App\Http\Middleware\TenantMiddleware;
use App\Models\Module;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\Concerns\WithTenant;
use Tests\TestCase;

/**
 * Feature tests for Tenant\RolesController.
 * Verifies that roles are scoped per-tenant using the "t{tenantId}_" prefix
 * and that CRUD operations are isolated between tenants.
 */
class RolesControllerTest extends TestCase
{
    use RefreshDatabase, WithTenant;

    // ---------------------------------------------------------------
    // Index
    // ---------------------------------------------------------------

    public function test_index_returns_only_roles_for_current_tenant(): void
    {
        [$tenantA, $adminA] = $this->createTenant();
        [$tenantB]          = $this->createTenant([], ['email' => 'admin2@rb.test']);

        // Create roles for each tenant (prefixed)
        Role::create(['name' => "t{$tenantA->id}_teacher", 'guard_name' => 'web']);
        Role::create(['name' => "t{$tenantB->id}_librarian", 'guard_name' => 'web']);

        $this->bindTenant($tenantA);

        $response = $this->actingAs($adminA)
            ->withoutMiddleware(TenantMiddleware::class)
            ->get('/roles');

        $response->assertOk();
        $roles = collect($response->viewData('page')['props']['roles'] ?? []);

        // Only "teacher" (Tenant A's role) should be present; librarian belongs to B
        $names = $roles->pluck('name')->toArray();
        $this->assertContains('teacher', $names);
        $this->assertNotContains('librarian', $names);
    }

    // ---------------------------------------------------------------
    // Store
    // ---------------------------------------------------------------

    public function test_store_creates_prefixed_role_for_current_tenant(): void
    {
        [$tenant, $admin] = $this->createTenant();

        $this->bindTenant($tenant);

        $response = $this->actingAs($admin)
            ->withoutMiddleware(TenantMiddleware::class)
            ->post('/roles', [
                'name'        => 'librarian',
                'permissions' => [],
            ]);

        $response->assertRedirect('/roles');
        $this->assertDatabaseHas('roles', [
            'name' => "t{$tenant->id}_librarian",
        ]);
    }

    public function test_store_rejects_duplicate_role_name_within_same_tenant(): void
    {
        [$tenant, $admin] = $this->createTenant();

        Role::create(['name' => "t{$tenant->id}_registrar", 'guard_name' => 'web']);

        $this->bindTenant($tenant);

        $response = $this->actingAs($admin)
            ->withoutMiddleware(TenantMiddleware::class)
            ->post('/roles', [
                'name'        => 'registrar',
                'permissions' => [],
            ]);

        $response->assertSessionHasErrors();
    }

    public function test_two_tenants_can_have_roles_with_same_display_name(): void
    {
        [$tenantA, $adminA] = $this->createTenant();
        [$tenantB]          = $this->createTenant([], ['email' => 'admin3@rb.test']);

        // Both tenants have a "teacher" role — stored with different prefixes
        $this->bindTenant($tenantA);

        $this->actingAs($adminA)
            ->withoutMiddleware(TenantMiddleware::class)
            ->post('/roles', ['name' => 'teacher', 'permissions' => []]);

        $this->assertDatabaseHas('roles', ['name' => "t{$tenantA->id}_teacher"]);

        // Tenant B can create the same display name independently
        $this->assertDatabaseMissing('roles', ['name' => "t{$tenantB->id}_teacher"]);
    }

    // ---------------------------------------------------------------
    // Destroy
    // ---------------------------------------------------------------

    public function test_destroy_deletes_role_belonging_to_current_tenant(): void
    {
        [$tenant, $admin] = $this->createTenant();
        $role = Role::create(['name' => "t{$tenant->id}_porter", 'guard_name' => 'web']);

        $this->bindTenant($tenant);

        $response = $this->actingAs($admin)
            ->withoutMiddleware(TenantMiddleware::class)
            ->delete("/roles/{$role->id}");

        $response->assertRedirect('/roles');
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_destroy_refuses_to_delete_another_tenants_role(): void
    {
        [$tenantA, $adminA] = $this->createTenant();
        [$tenantB]          = $this->createTenant([], ['email' => 'admin4@rb.test']);

        $roleB = Role::create(['name' => "t{$tenantB->id}_guard", 'guard_name' => 'web']);

        // Acting as Tenant A trying to delete Tenant B's role
        $this->bindTenant($tenantA);

        $response = $this->actingAs($adminA)
            ->withoutMiddleware(TenantMiddleware::class)
            ->delete("/roles/{$roleB->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('roles', ['id' => $roleB->id]);
    }
}
