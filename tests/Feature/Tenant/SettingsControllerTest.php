<?php

declare(strict_types=1);

namespace Tests\Feature\Tenant;

use App\Http\Middleware\TenantMiddleware;
use App\Models\Module;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithTenant;
use Tests\TestCase;

/**
 * Feature tests for Tenant\SettingsController.
 * Covers general settings read/write, module toggle, subscription page.
 */
class SettingsControllerTest extends TestCase
{
    use RefreshDatabase, WithTenant;

    // ---------------------------------------------------------------
    // Settings index
    // ---------------------------------------------------------------

    public function test_settings_index_requires_auth(): void
    {
        $response = $this->get('/settings');

        $response->assertRedirect('/login');
    }

    public function test_settings_index_is_accessible_to_authenticated_tenant_admin(): void
    {
        [$tenant, $admin] = $this->createTenant();

        $this->bindTenant($tenant);

        $response = $this->actingAs($admin)
            ->withoutMiddleware(TenantMiddleware::class)
            ->get('/settings');

        $response->assertOk();
    }

    // ---------------------------------------------------------------
    // General settings
    // ---------------------------------------------------------------

    public function test_general_settings_page_renders(): void
    {
        [$tenant, $admin] = $this->createTenant();

        $this->bindTenant($tenant);

        $response = $this->actingAs($admin)
            ->withoutMiddleware(TenantMiddleware::class)
            ->get('/settings/general');

        $response->assertOk();
    }

    public function test_update_general_settings_persists_tenant_name(): void
    {
        [$tenant, $admin] = $this->createTenant();

        $this->bindTenant($tenant);

        $response = $this->actingAs($admin)
            ->withoutMiddleware(TenantMiddleware::class)
            ->put('/settings/general', [
                'name'  => 'Updated School Name',
                'email' => 'updated@school.test',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tenants', [
            'id'   => $tenant->id,
            'name' => 'Updated School Name',
        ]);
    }

    // ---------------------------------------------------------------
    // Modules settings
    // ---------------------------------------------------------------

    public function test_modules_settings_page_renders(): void
    {
        [$tenant, $admin] = $this->createTenant();

        $this->bindTenant($tenant);

        $response = $this->actingAs($admin)
            ->withoutMiddleware(TenantMiddleware::class)
            ->get('/settings/modules');

        $response->assertOk();
    }

    public function test_toggle_module_enables_a_disabled_module(): void
    {
        [$tenant, $admin] = $this->createTenant();

        $module = Module::create([
            'name'                 => 'Library',
            'key'                  => 'library',
            'allowed_tenant_types' => ['school'],
            'dependencies'         => [],
            'default_permissions'  => [],
            'settings_schema'      => [],
            'is_core'              => false,
            'is_active'            => true,
            'is_globally_disabled' => false,
            'sort_order'           => 10,
        ]);

        $this->bindTenant($tenant);

        $response = $this->actingAs($admin)
            ->withoutMiddleware(TenantMiddleware::class)
            ->patch('/settings/modules/toggle', ['module_id' => $module->id, 'enabled' => true]);

        $response->assertRedirect();
        $this->assertTrue(
            $tenant->modules()->where('modules.id', $module->id)->wherePivot('is_enabled', true)->exists()
        );
    }

    // ---------------------------------------------------------------
    // Subscription settings
    // ---------------------------------------------------------------

    public function test_subscription_settings_page_renders(): void
    {
        [$tenant, $admin] = $this->createTenant();

        $this->bindTenant($tenant);

        $response = $this->actingAs($admin)
            ->withoutMiddleware(TenantMiddleware::class)
            ->get('/settings/subscription');

        $response->assertOk();
    }

    // ---------------------------------------------------------------
    // Permissions settings
    // ---------------------------------------------------------------

    public function test_permissions_settings_page_renders(): void
    {
        [$tenant, $admin] = $this->createTenant();

        $this->bindTenant($tenant);

        $response = $this->actingAs($admin)
            ->withoutMiddleware(TenantMiddleware::class)
            ->get('/settings/permissions');

        $response->assertOk();
    }
}
