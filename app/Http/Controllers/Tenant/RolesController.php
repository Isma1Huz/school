<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\PermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Manages tenant-scoped roles.
 *
 * To keep roles isolated between tenants without modifying Spatie's tables,
 * role names are stored with a prefix "t{tenantId}_" in the database.
 * The prefix is stripped before sending data to the frontend and reapplied
 * before writing back to the database.
 */
class RolesController extends Controller
{
    public function __construct(private readonly PermissionService $permissionService) {}

    public function index(): Response
    {
        $prefix = $this->tenantRolePrefix();

        $roles = Role::withCount('permissions')
            ->where('guard_name', 'web')
            ->where('name', 'like', $prefix . '%')
            ->get()
            ->map(fn ($r) => [
                'id'                => $r->id,
                'name'              => $this->stripPrefix($r->name),
                'guard_name'        => $r->guard_name,
                'permissions_count' => $r->permissions_count,
            ]);

        return Inertia::render('Tenant/Roles/Index', compact('roles'));
    }

    public function create(): Response
    {
        return Inertia::render('Tenant/Roles/Create', [
            'permissionNodes' => $this->buildPermissionNodes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'permissions' => ['array'],
        ]);

        $fullName = $this->tenantRolePrefix() . $data['name'];

        $request->validate([
            'name' => [
                function ($attribute, $value, $fail) use ($fullName) {
                    if (Role::where('name', $fullName)->where('guard_name', 'web')->exists()) {
                        $fail('A role with this name already exists.');
                    }
                },
            ],
        ]);

        $role = Role::create(['name' => $fullName, 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success', 'Role created.');
    }

    public function edit(Role $role): Response
    {
        $this->authorizeRole($role);
        $role->load('permissions:name');

        return Inertia::render('Tenant/Roles/Edit', [
            'role'            => [
                'id'          => $role->id,
                'name'        => $this->stripPrefix($role->name),
                'permissions' => $role->permissions->toArray(),
            ],
            'permissionNodes' => $this->buildPermissionNodes(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->authorizeRole($role);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'permissions' => ['array'],
        ]);

        $fullName = $this->tenantRolePrefix() . $data['name'];

        // Allow same name on update (ignore self)
        $request->validate([
            'name' => [
                function ($attribute, $value, $fail) use ($fullName, $role) {
                    if (Role::where('name', $fullName)->where('guard_name', 'web')->where('id', '!=', $role->id)->exists()) {
                        $fail('A role with this name already exists.');
                    }
                },
            ],
        ]);

        $role->update(['name' => $fullName]);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorizeRole($role);
        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role deleted.');
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function tenantRolePrefix(): string
    {
        $tenant = app('tenant');
        return "t{$tenant->id}_";
    }

    private function stripPrefix(string $name): string
    {
        $prefix = $this->tenantRolePrefix();
        return str_starts_with($name, $prefix) ? substr($name, strlen($prefix)) : $name;
    }

    /** Ensure the role belongs to the current tenant — abort 403 otherwise. */
    private function authorizeRole(Role $role): void
    {
        if (!str_starts_with($role->name, $this->tenantRolePrefix())) {
            abort(403, 'You do not have access to this role.');
        }
    }

    private function buildPermissionNodes(): array
    {
        $byModule = $this->permissionService->getPermissionsByModule();

        return collect($byModule)
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
    }
}
