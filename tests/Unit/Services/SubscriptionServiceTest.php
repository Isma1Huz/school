<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Module;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithTenant;
use Tests\TestCase;

/**
 * Unit tests for SubscriptionService.
 * Covers: assignPlan, cancelSubscription, isValid, daysRemaining.
 */
class SubscriptionServiceTest extends TestCase
{
    use RefreshDatabase, WithTenant;

    private SubscriptionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SubscriptionService::class);
    }

    // ---------------------------------------------------------------
    // assignPlan
    // ---------------------------------------------------------------

    public function test_assign_plan_updates_subscription_fields(): void
    {
        [$tenant] = $this->createTenant();
        $plan     = $this->createPlan();

        $this->service->assignPlan($tenant, $plan, 'monthly');

        $tenant->refresh();

        $this->assertEquals($plan->id, $tenant->subscription_plan_id);
        $this->assertEquals('active', $tenant->subscription_status);
        $this->assertEquals('monthly', $tenant->billing_cycle);
        $this->assertEquals($plan->max_users, $tenant->max_users);
        $this->assertEquals($plan->max_storage_mb, $tenant->max_storage_mb);
    }

    public function test_assign_plan_yearly_sets_one_year_end_date(): void
    {
        [$tenant] = $this->createTenant();
        $plan     = $this->createPlan();

        $this->service->assignPlan($tenant, $plan, 'yearly');

        $tenant->refresh();

        $this->assertEquals(
            now()->addYear()->toDateString(),
            $tenant->subscription_end_date->toDateString(),
        );
    }

    public function test_assign_plan_monthly_sets_one_month_end_date(): void
    {
        [$tenant] = $this->createTenant();
        $plan     = $this->createPlan();

        $this->service->assignPlan($tenant, $plan, 'monthly');

        $tenant->refresh();

        $this->assertEquals(
            now()->addMonth()->toDateString(),
            $tenant->subscription_end_date->toDateString(),
        );
    }

    public function test_assign_plan_syncs_included_modules(): void
    {
        [$tenant] = $this->createTenant();
        $plan     = $this->createPlan();
        $module   = Module::create([
            'name'                 => 'Finance',
            'key'                  => 'finance',
            'allowed_tenant_types' => ['school'],
            'dependencies'         => [],
            'default_permissions'  => [],
            'settings_schema'      => [],
            'is_core'              => false,
            'is_active'            => true,
            'is_globally_disabled' => false,
            'sort_order'           => 10,
        ]);

        // Attach module to plan
        $plan->includedModules()->attach($module->id);

        $this->service->assignPlan($tenant, $plan, 'monthly');

        $this->assertTrue(
            $tenant->modules()->where('modules.id', $module->id)->wherePivot('is_enabled', true)->exists()
        );
    }

    // ---------------------------------------------------------------
    // cancelSubscription
    // ---------------------------------------------------------------

    public function test_cancel_subscription_sets_status_to_cancelled(): void
    {
        [$tenant] = $this->createTenant();
        $plan     = $this->createPlan();
        $this->service->assignPlan($tenant, $plan);

        $this->service->cancelSubscription($tenant);

        $tenant->refresh();

        $this->assertEquals('cancelled', $tenant->subscription_status);
    }

    // ---------------------------------------------------------------
    // isValid
    // ---------------------------------------------------------------

    public function test_is_valid_returns_true_for_active_subscription_with_future_end(): void
    {
        [$tenant] = $this->createTenant([
            'subscription_status'   => 'active',
            'subscription_end_date' => now()->addMonth()->toDateString(),
        ]);

        $this->assertTrue($this->service->isValid($tenant));
    }

    public function test_is_valid_returns_false_for_expired_end_date(): void
    {
        [$tenant] = $this->createTenant([
            'subscription_status'   => 'active',
            'subscription_end_date' => now()->subDay()->toDateString(),
        ]);

        $this->assertFalse($this->service->isValid($tenant));
    }

    public function test_is_valid_returns_false_for_cancelled_status(): void
    {
        [$tenant] = $this->createTenant([
            'subscription_status' => 'cancelled',
        ]);

        $this->assertFalse($this->service->isValid($tenant));
    }

    public function test_is_valid_returns_true_for_active_with_no_end_date(): void
    {
        [$tenant] = $this->createTenant([
            'subscription_status'   => 'active',
            'subscription_end_date' => null,
        ]);

        $this->assertTrue($this->service->isValid($tenant));
    }

    // ---------------------------------------------------------------
    // daysRemaining
    // ---------------------------------------------------------------

    public function test_days_remaining_returns_null_for_no_end_date(): void
    {
        [$tenant] = $this->createTenant(['subscription_end_date' => null]);

        $this->assertNull($this->service->daysRemaining($tenant));
    }

    public function test_days_remaining_returns_correct_count(): void
    {
        [$tenant] = $this->createTenant([
            'subscription_end_date' => now()->addDays(30)->toDateString(),
        ]);

        $days = $this->service->daysRemaining($tenant);

        $this->assertEqualsWithDelta(30, $days, 1); // allow 1 day tolerance
    }

    public function test_days_remaining_is_negative_for_expired(): void
    {
        [$tenant] = $this->createTenant([
            'subscription_end_date' => now()->subDays(5)->toDateString(),
        ]);

        $days = $this->service->daysRemaining($tenant);

        $this->assertLessThan(0, $days);
    }

    // ---------------------------------------------------------------
    // getPlansForTenantType
    // ---------------------------------------------------------------

    public function test_get_plans_for_tenant_type_returns_active_plans_only(): void
    {
        SubscriptionPlan::create([
            'name'                    => 'School Basic',
            'code'                    => 'school-basic',
            'applicable_tenant_types' => ['school'],
            'price_monthly'           => 50,
            'price_yearly'            => 500,
            'max_users'               => 20,
            'max_storage_mb'          => 1024,
            'features'                => [],
            'is_active'               => true,
            'sort_order'              => 1,
        ]);

        SubscriptionPlan::create([
            'name'                    => 'School Inactive',
            'code'                    => 'school-inactive',
            'applicable_tenant_types' => ['school'],
            'price_monthly'           => 0,
            'price_yearly'            => 0,
            'max_users'               => 5,
            'max_storage_mb'          => 256,
            'features'                => [],
            'is_active'               => false,
            'sort_order'              => 2,
        ]);

        $plans = $this->service->getPlansForTenantType('school');

        $this->assertGreaterThanOrEqual(1, $plans->count());
        $this->assertTrue($plans->every(fn ($p) => $p->is_active));
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function createPlan(): SubscriptionPlan
    {
        return SubscriptionPlan::create([
            'name'                    => 'Test Plan',
            'code'                    => 'test-plan-' . uniqid(),
            'applicable_tenant_types' => ['school'],
            'price_monthly'           => 49.99,
            'price_yearly'            => 499.99,
            'max_users'               => 100,
            'max_storage_mb'          => 5120,
            'features'                => [],
            'is_active'               => true,
            'sort_order'              => 0,
        ]);
    }
}
