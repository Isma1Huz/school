<?php

declare(strict_types=1);

namespace Tests\Feature\Tenant;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Core\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithTenant;
use Tests\TestCase;

/**
 * Tests for TenantScope — ensures global scope correctly restricts queries
 * to the bound tenant and is entirely absent when no tenant is bound.
 */
class TenantScopeTest extends TestCase
{
    use RefreshDatabase, WithTenant;

    // ---------------------------------------------------------------
    // Scope applies when tenant is bound
    // ---------------------------------------------------------------

    public function test_scope_filters_users_to_bound_tenant(): void
    {
        [$tenantA] = $this->createTenant();
        [$tenantB] = $this->createTenant([], ['email' => 'admin-b@test.test']);

        $userA = $this->createTenantUser($tenantA);
        $userB = $this->createTenantUser($tenantB);

        // Bind tenant A
        $this->bindTenant($tenantA);

        $ids = User::pluck('id');

        $this->assertContains($userA->id, $ids);
        $this->assertNotContains($userB->id, $ids);
    }

    public function test_scope_is_absent_when_no_tenant_bound(): void
    {
        [$tenantA] = $this->createTenant();
        [$tenantB] = $this->createTenant([], ['email' => 'admin-b2@test.test']);

        $userA = $this->createTenantUser($tenantA);
        $userB = $this->createTenantUser($tenantB);

        // Remove tenant from container
        $this->clearTenant();

        $ids = User::withoutGlobalScopes()->pluck('id');

        $this->assertContains($userA->id, $ids);
        $this->assertContains($userB->id, $ids);
    }

    // ---------------------------------------------------------------
    // withoutGlobalScopes bypasses the scope
    // ---------------------------------------------------------------

    public function test_without_global_scopes_returns_all_users(): void
    {
        [$tenantA] = $this->createTenant();
        [$tenantB] = $this->createTenant([], ['email' => 'admin-b3@test.test']);

        $userA = $this->createTenantUser($tenantA);
        $userB = $this->createTenantUser($tenantB);

        // Bind tenant A — normally scoped
        $this->bindTenant($tenantA);

        $allIds = User::withoutGlobalScopes()->pluck('id');

        $this->assertContains($userA->id, $allIds);
        $this->assertContains($userB->id, $allIds);
    }

    // ---------------------------------------------------------------
    // forTenant scope helper
    // ---------------------------------------------------------------

    public function test_for_tenant_scope_restricts_to_given_tenant(): void
    {
        [$tenantA] = $this->createTenant();
        [$tenantB] = $this->createTenant([], ['email' => 'admin-b4@test.test']);

        $userA = $this->createTenantUser($tenantA);
        $userB = $this->createTenantUser($tenantB);

        // No tenant bound — use explicit scope
        $this->clearTenant();

        $idsA = User::withoutGlobalScopes()->forTenant($tenantA)->pluck('id');

        $this->assertContains($userA->id, $idsA);
        $this->assertNotContains($userB->id, $idsA);
    }

    // ---------------------------------------------------------------
    // TenantScope::apply logic inspection
    // ---------------------------------------------------------------

    public function test_scope_adds_where_clause_when_tenant_bound(): void
    {
        [$tenant] = $this->createTenant();

        $scope   = new TenantScope();
        $user    = new User();
        $builder = User::withoutGlobalScopes()->getQuery()->newQuery();
        $builder->setModel($user);

        $scope->apply($builder, $user);

        $wheres = collect($builder->getQuery()->wheres);

        $this->assertTrue(
            $wheres->contains(fn ($w) => ($w['column'] ?? '') === 'users.tenant_id' && $w['value'] === $tenant->id),
            'TenantScope should add a WHERE tenant_id = ? clause.',
        );
    }

    public function test_scope_adds_no_where_clause_when_no_tenant_bound(): void
    {
        $this->clearTenant();

        $scope   = new TenantScope();
        $user    = new User();
        $builder = User::withoutGlobalScopes()->getQuery()->newQuery();
        $builder->setModel($user);

        $scope->apply($builder, $user);

        $this->assertCount(0, $builder->getQuery()->wheres);
    }
}
