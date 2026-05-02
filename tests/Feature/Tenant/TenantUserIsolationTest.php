<?php

declare(strict_types=1);

namespace Tests\Feature\Tenant;

use App\Http\Middleware\TenantMiddleware;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithTenant;
use Tests\TestCase;

/**
 * Cross-tenant isolation tests.
 *
 * Verifies that a user from Tenant A cannot access resources belonging to Tenant B
 * and that the TenantScope correctly partitions data between tenants.
 */
class TenantUserIsolationTest extends TestCase
{
    use RefreshDatabase, WithTenant;

    // ---------------------------------------------------------------
    // Data isolation at the ORM level
    // ---------------------------------------------------------------

    public function test_tenant_a_cannot_query_tenant_b_users_via_orm(): void
    {
        [$tenantA, $adminA] = $this->createTenant();
        [$tenantB, $adminB] = $this->createTenant([], ['email' => 'admin2@test.test']);

        $staffB = $this->createTenantUser($tenantB);

        // Bind Tenant A
        $this->bindTenant($tenantA);

        // Under Tenant A scope, Tenant B's user should be invisible
        $ids = User::pluck('id');

        $this->assertNotContains($staffB->id, $ids);
        $this->assertNotContains($adminB->id, $ids);
    }

    public function test_tenant_b_cannot_query_tenant_a_users_via_orm(): void
    {
        [$tenantA, $adminA] = $this->createTenant();
        [$tenantB, $adminB] = $this->createTenant([], ['email' => 'admin3@test.test']);

        $staffA = $this->createTenantUser($tenantA);

        // Bind Tenant B
        $this->bindTenant($tenantB);

        $ids = User::pluck('id');

        $this->assertNotContains($staffA->id, $ids);
        $this->assertNotContains($adminA->id, $ids);
    }

    // ---------------------------------------------------------------
    // HTTP isolation — tenant user cannot reach another tenant's route
    // ---------------------------------------------------------------

    public function test_tenant_a_admin_cannot_access_tenant_b_users_endpoint(): void
    {
        [$tenantA, $adminA] = $this->createTenant();
        [$tenantB, $adminB] = $this->createTenant([], ['email' => 'admin4@test.test']);

        $staffB = $this->createTenantUser($tenantB);

        // Simulate admin A's request — bind Tenant A to container
        $this->bindTenant($tenantA);

        $response = $this->actingAs($adminA)
            ->withoutMiddleware(TenantMiddleware::class)
            ->get("/users/{$staffB->id}/edit");

        // Tenant A doesn't have access to Tenant B's user → 404 from findOrFail
        $response->assertNotFound();
    }

    // ---------------------------------------------------------------
    // Super-admin bypasses tenant scope
    // ---------------------------------------------------------------

    public function test_super_admin_can_query_all_users_without_scope(): void
    {
        [$tenantA, $adminA] = $this->createTenant();
        [$tenantB, $adminB] = $this->createTenant([], ['email' => 'admin5@test.test']);

        $staffA = $this->createTenantUser($tenantA);
        $staffB = $this->createTenantUser($tenantB);

        // No tenant bound for super-admin context
        $this->clearTenant();

        $allIds = User::withoutGlobalScopes()->pluck('id');

        $this->assertContains($staffA->id, $allIds);
        $this->assertContains($staffB->id, $allIds);
    }

    // ---------------------------------------------------------------
    // Tenant relation integrity
    // ---------------------------------------------------------------

    public function test_users_created_under_a_tenant_have_correct_tenant_id(): void
    {
        [$tenant, $admin] = $this->createTenant();
        $staff = $this->createTenantUser($tenant);

        $this->assertEquals($tenant->id, $admin->tenant_id);
        $this->assertEquals($tenant->id, $staff->tenant_id);
    }

    public function test_users_returned_by_tenant_users_relation_belong_to_that_tenant(): void
    {
        [$tenantA, $adminA] = $this->createTenant();
        [$tenantB, $adminB] = $this->createTenant([], ['email' => 'admin6@test.test']);

        $staffA = $this->createTenantUser($tenantA);

        $tenantAUserIds = $tenantA->users()->withoutGlobalScopes()->pluck('id');

        $this->assertContains($staffA->id, $tenantAUserIds);
        $this->assertNotContains($adminB->id, $tenantAUserIds);
    }

    // ---------------------------------------------------------------
    // Subscription enforcement isolation
    // ---------------------------------------------------------------

    public function test_expired_tenant_subscription_is_invalid(): void
    {
        [$tenant] = $this->createTenant([
            'subscription_status'   => 'active',
            'subscription_end_date' => now()->subDay()->toDateString(),
        ]);

        $this->assertFalse($tenant->isSubscriptionValid());
    }

    public function test_active_tenant_subscription_is_valid(): void
    {
        [$tenant] = $this->createTenant([
            'subscription_status'   => 'active',
            'subscription_end_date' => now()->addMonth()->toDateString(),
        ]);

        $this->assertTrue($tenant->isSubscriptionValid());
    }

    public function test_trial_subscription_with_future_end_date_is_valid(): void
    {
        [$tenant] = $this->createTenant([
            'subscription_status'   => 'trial',
            'subscription_end_date' => now()->addWeek()->toDateString(),
        ]);

        $this->assertTrue($tenant->isSubscriptionValid());
    }

    public function test_cancelled_subscription_is_invalid(): void
    {
        [$tenant] = $this->createTenant([
            'subscription_status' => 'cancelled',
        ]);

        $this->assertFalse($tenant->isSubscriptionValid());
    }
}
