<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ModuleService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Spatie roles
        foreach (['super_admin', 'soc_admin', 'tenant_admin', 'staff'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // Create a demo subscription plan
        $plan = SubscriptionPlan::firstOrCreate(
            ['code' => 'demo'],
            [
                'name'                    => 'Demo',
                'description'             => 'Demo plan for development',
                'applicable_tenant_types' => ['school'],
                'price_monthly'           => 0,
                'price_yearly'            => 0,
                'max_users'               => 0,
                'max_storage_mb'          => 1024,
                'features'                => [],
                'is_active'               => true,
                'sort_order'              => 0,
            ]
        );

        // Create a demo tenant
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'demo'],
            [
                'name'                    => 'Demo School',
                'type'                    => 'school',
                'email'                   => 'admin@demo.test',
                'status'                  => 'active',
                'subscription_plan_id'    => $plan->id,
                'subscription_status'     => 'active',
                'subscription_start_date' => now()->toDateString(),
                'subscription_end_date'   => now()->addYear()->toDateString(),
                'billing_cycle'           => 'yearly',
                'max_users'               => 0,
                'max_storage_mb'          => 1024,
            ]
        );

        // Create a super-admin user (no tenant scope)
        $superAdmin = User::withoutGlobalScopes()->firstOrCreate(
            ['email' => 'superadmin@schoolzee.test'],
            [
                'name'      => 'Super Admin',
                'password'  => Hash::make('password'),
                'user_type' => 'super_admin',
                'is_active' => true,
            ]
        );
        $superAdmin->syncRoles(['super_admin']);

        // Create a SOC admin user (no tenant scope)
        $socAdmin = User::withoutGlobalScopes()->firstOrCreate(
            ['email' => 'soc@schoolzee.test'],
            [
                'name'      => 'SOC Admin',
                'password'  => Hash::make('password'),
                'user_type' => 'soc_admin',
                'is_active' => true,
            ]
        );
        $socAdmin->syncRoles(['soc_admin']);

        // Create a tenant admin for the demo school
        $tenantAdmin = User::withoutGlobalScopes()->where('email', 'admin@demo.test')->first();
        if (!$tenantAdmin) {
            $tenantAdmin = new User([
                'name'      => 'Demo Admin',
                'password'  => Hash::make('password'),
                'user_type' => 'tenant_admin',
                'is_active' => true,
                'email'     => 'admin@demo.test',
            ]);
            $tenantAdmin->tenant_id = $tenant->id;
            $tenantAdmin->save();
        }
        $tenantAdmin->syncRoles(['tenant_admin']);

        // Register the Core module from its module.json and enable it for the demo tenant
        $moduleService = app(ModuleService::class);

        if (file_exists(app_path('Modules/Core/module.json'))) {
            $coreModule = $moduleService->registerFromManifest('Core');

            if (!$tenant->modules()->where('modules.id', $coreModule->id)->exists()) {
                $tenant->modules()->attach($coreModule->id, ['is_enabled' => true]);
            } else {
                $tenant->modules()->updateExistingPivot($coreModule->id, ['is_enabled' => true]);
            }
        }
    }
}

