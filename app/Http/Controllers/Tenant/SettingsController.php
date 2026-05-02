<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Tenant;
use App\Services\PermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function __construct(private readonly PermissionService $permissionService) {}

    public function index(): Response
    {
        return Inertia::render('Tenant/Settings/Index');
    }

    public function general(): Response
    {
        $tenant = app('tenant');

        return Inertia::render('Tenant/Settings/General', [
            'tenant' => $this->formatTenant($tenant),
        ]);
    }

    public function updateGeneral(Request $request): RedirectResponse
    {
        /** @var Tenant $tenant */
        $tenant = app('tenant');

        $data = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['nullable', 'email', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:50'],
            'address'         => ['nullable', 'string', 'max:500'],
            'primary_color'   => ['nullable', 'string', 'max:20'],
            'secondary_color' => ['nullable', 'string', 'max:20'],
        ]);

        $tenant->update($data);

        return back()->with('success', 'Settings saved.');
    }

    public function modules(): Response
    {
        /** @var Tenant $tenant */
        $tenant = app('tenant');
        $enabledModuleIds = $tenant->modules()->wherePivot('is_enabled', true)->pluck('modules.id')->toArray();
        $modules = Module::whereNull('deleted_at')->get()->map(fn ($m) => [
            'id'           => $m->id,
            'key'          => $m->key,
            'name'         => $m->name,
            'description'  => $m->description,
            'is_core'      => $m->is_core,
            'dependencies' => $m->dependencies ?? [],
        ]);

        return Inertia::render('Tenant/Settings/Modules', [
            'modules'          => $modules,
            'enabledModuleIds' => $enabledModuleIds,
        ]);
    }

    public function toggleModule(Request $request): RedirectResponse
    {
        /** @var Tenant $tenant */
        $tenant = app('tenant');

        $data = $request->validate([
            'module_id'  => ['required', 'integer', 'exists:modules,id'],
            'is_enabled' => ['required', 'boolean'],
        ]);

        $module = Module::findOrFail($data['module_id']);
        if ($module->is_core) {
            return back()->withErrors(['module' => 'Core modules cannot be disabled.']);
        }

        if ($tenant->modules()->where('modules.id', $module->id)->exists()) {
            $tenant->modules()->updateExistingPivot($module->id, ['is_enabled' => $data['is_enabled']]);
        } else {
            $tenant->modules()->attach($module->id, ['is_enabled' => $data['is_enabled']]);
        }

        return back()->with('success', 'Module updated.');
    }

    public function permissions(): Response
    {
        $permissionsByModule = $this->permissionService->getPermissionsByModule();
        $permissionNodes     = collect($permissionsByModule)
            ->map(fn ($perms, $module) => [
                'key'      => $module,
                'label'    => ucfirst($module),
                'children' => collect($perms)->map(fn ($p) => [
                    'key'   => $p,
                    'label' => ucwords(str_replace([':', '_'], [' ', ' '], $p)),
                ])->values()->toArray(),
            ])
            ->values()
            ->toArray();

        // Currently selected permissions (tenant admin role)
        $selectedPermissions = \Spatie\Permission\Models\Permission::all()->pluck('name')->toArray();

        return Inertia::render('Tenant/Settings/Permissions', [
            'permissionNodes'     => $permissionNodes,
            'selectedPermissions' => $selectedPermissions,
        ]);
    }

    public function updatePermissions(Request $request): RedirectResponse
    {
        $request->validate(['permissions' => ['array']]);
        // In a full implementation, this would sync permissions for the tenant's roles
        return back()->with('success', 'Permissions updated.');
    }

    public function subscription(): Response
    {
        /** @var Tenant $tenant */
        $tenant    = app('tenant');
        $usedUsers = $tenant->users()->count();

        // Sum actual media file sizes for this tenant (bytes → MB)
        $usedStorageMb = (int) round(
            \Spatie\MediaLibrary\MediaCollections\Models\Media::where('tenant_id', $tenant->id)
                ->sum('size') / (1024 * 1024)
        );

        return Inertia::render('Tenant/Settings/Subscription', [
            'tenant'        => $this->formatTenant($tenant),
            'usedUsers'     => $usedUsers,
            'usedStorageMb' => $usedStorageMb,
        ]);
    }

    private function formatTenant(Tenant $tenant): array
    {
        return [
            'id'                    => $tenant->id,
            'name'                  => $tenant->name,
            'slug'                  => $tenant->slug,
            'type'                  => $tenant->type,
            'email'                 => $tenant->email,
            'phone'                 => $tenant->phone ?? null,
            'address'               => $tenant->address ?? null,
            'primary_color'         => $tenant->primary_color ?? '#800020',
            'secondary_color'       => $tenant->secondary_color ?? '#FFD700',
            'logo'                  => $tenant->logo ?? null,
            'status'                => $tenant->status,
            'subscription_status'   => $tenant->subscription_status,
            'subscription_start_date' => $tenant->subscription_start_date?->toDateString(),
            'subscription_end_date'   => $tenant->subscription_end_date?->toDateString(),
            'billing_cycle'           => $tenant->billing_cycle ?? null,
            'max_users'               => $tenant->max_users,
            'max_storage_mb'          => $tenant->max_storage_mb,
        ];
    }
}
