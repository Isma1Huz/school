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

class RolesController extends Controller
{
    public function __construct(private readonly PermissionService $permissionService) {}

    public function index(): Response
    {
        $roles = Role::withCount('permissions')
            ->where('guard_name', 'web')
            ->get(['id', 'name', 'guard_name'])
            ->map(fn ($r) => [
                'id'                => $r->id,
                'name'              => $r->name,
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
            'name'        => ['required', 'string', 'max:125', 'unique:roles,name'],
            'permissions' => ['array'],
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success', 'Role created.');
    }

    public function edit(Role $role): Response
    {
        $role->load('permissions:name');

        return Inertia::render('Tenant/Roles/Edit', [
            'role'            => [
                'id'          => $role->id,
                'name'        => $role->name,
                'permissions' => $role->permissions->toArray(),
            ],
            'permissionNodes' => $this->buildPermissionNodes(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:125', 'unique:roles,name,' . $role->id],
            'permissions' => ['array'],
        ]);

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role deleted.');
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
