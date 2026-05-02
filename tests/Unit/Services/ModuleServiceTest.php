<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Module;
use App\Models\Tenant;
use App\Services\ModuleService;
use App\Services\PermissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithTenant;
use Tests\TestCase;

/**
 * Unit / integration tests for ModuleService.
 * Covers: enable, disable, dependency resolution, canEnable checks.
 */
class ModuleServiceTest extends TestCase
{
    use RefreshDatabase, WithTenant;

    private ModuleService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ModuleService::class);
    }

    // ---------------------------------------------------------------
    // enableModule
    // ---------------------------------------------------------------

    public function test_enable_module_attaches_module_to_tenant(): void
    {
        [$tenant] = $this->createTenant();
        $module   = $this->createModule('analytics');

        $this->service->enableModule($tenant, $module);

        $this->assertTrue(
            $tenant->modules()->where('modules.id', $module->id)->wherePivot('is_enabled', true)->exists()
        );
    }

    public function test_enable_module_enables_dependency_automatically(): void
    {
        [$tenant] = $this->createTenant();
        $base = $this->createModule('base');
        $dep  = $this->createModule('dep', dependencies: ['base']);

        $this->service->enableModule($tenant, $dep);

        // Both the requested module and its dependency should be enabled
        $this->assertTrue($this->service->isModuleEnabled($tenant, 'dep'));
        $this->assertTrue($this->service->isModuleEnabled($tenant, 'base'));
    }

    public function test_enable_module_throws_when_tenant_type_not_allowed(): void
    {
        $tenant = Tenant::create([
            'name'                => 'College',
            'slug'                => 'college-' . uniqid(),
            'type'                => 'college',
            'status'              => 'active',
            'subscription_status' => 'active',
            'max_users'           => 10,
            'max_storage_mb'      => 512,
        ]);

        $module = $this->createModule('school-only', allowedTypes: ['school']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/not available for tenant type/');

        $this->service->enableModule($tenant, $module);
    }

    // ---------------------------------------------------------------
    // disableModule
    // ---------------------------------------------------------------

    public function test_disable_module_sets_is_enabled_false(): void
    {
        [$tenant] = $this->createTenant();
        $module   = $this->createModule('reports');

        $this->service->enableModule($tenant, $module);
        $this->service->disableModule($tenant, $module);

        $this->assertFalse($this->service->isModuleEnabled($tenant, 'reports'));
    }

    public function test_disable_module_throws_when_dependent_module_is_enabled(): void
    {
        [$tenant] = $this->createTenant();
        $base     = $this->createModule('core-base');
        $dep      = $this->createModule('needs-core', dependencies: ['core-base']);

        $this->service->enableModule($tenant, $base);
        $this->service->enableModule($tenant, $dep);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/required by/');

        $this->service->disableModule($tenant, $base);
    }

    // ---------------------------------------------------------------
    // canEnableModule
    // ---------------------------------------------------------------

    public function test_can_enable_module_returns_true_when_dependencies_met(): void
    {
        [$tenant] = $this->createTenant();
        $base     = $this->createModule('prereq');
        $dep      = $this->createModule('needs-prereq', dependencies: ['prereq']);

        // Enable the prerequisite first
        $this->service->enableModule($tenant, $base);

        $this->assertTrue($this->service->canEnableModule($dep, $tenant));
    }

    public function test_can_enable_module_returns_false_when_dependency_missing(): void
    {
        [$tenant] = $this->createTenant();
        $this->createModule('missing-dep');
        $dep = $this->createModule('needs-missing', dependencies: ['missing-dep']);

        $this->assertFalse($this->service->canEnableModule($dep, $tenant));
    }

    // ---------------------------------------------------------------
    // isModuleEnabledForCurrentTenant
    // ---------------------------------------------------------------

    public function test_is_module_enabled_for_current_tenant_returns_true(): void
    {
        [$tenant] = $this->createTenant();
        $module   = $this->createModule('finance');

        $this->service->enableModule($tenant, $module);

        $this->assertTrue($this->service->isModuleEnabledForCurrentTenant('finance'));
    }

    public function test_is_module_enabled_for_current_tenant_returns_false_when_not_enabled(): void
    {
        [$tenant] = $this->createTenant();
        $this->createModule('inventory');

        $this->assertFalse($this->service->isModuleEnabledForCurrentTenant('inventory'));
    }

    public function test_is_module_enabled_for_current_tenant_returns_false_when_no_tenant_bound(): void
    {
        $this->clearTenant();

        $this->assertFalse($this->service->isModuleEnabledForCurrentTenant('any-module'));
    }

    // ---------------------------------------------------------------
    // enableCoreModulesForTenant
    // ---------------------------------------------------------------

    public function test_enable_core_modules_enables_all_core_modules(): void
    {
        [$tenant] = $this->createTenant();
        $core1    = $this->createModule('sys-core-a', isCore: true);
        $core2    = $this->createModule('sys-core-b', isCore: true);

        $this->service->enableCoreModulesForTenant($tenant);

        $this->assertTrue($this->service->isModuleEnabled($tenant, 'sys-core-a'));
        $this->assertTrue($this->service->isModuleEnabled($tenant, 'sys-core-b'));
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function createModule(
        string $key,
        array $dependencies = [],
        array $allowedTypes = ['school'],
        bool  $isCore = false,
    ): Module {
        return Module::create([
            'name'                 => ucfirst($key),
            'key'                  => $key,
            'allowed_tenant_types' => $allowedTypes,
            'dependencies'         => $dependencies,
            'default_permissions'  => [],
            'settings_schema'      => [],
            'is_core'              => $isCore,
            'is_active'            => true,
            'is_globally_disabled' => false,
            'sort_order'           => 100,
        ]);
    }
}
