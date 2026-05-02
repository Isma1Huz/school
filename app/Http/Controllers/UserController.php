<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function index(): Response
    {
        $tenant = app('tenant');
        $users  = $this->userService->listUsers($tenant);

        return Inertia::render('Tenant/Users/Index', [
            'users' => $users,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Tenant/Users/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = app('tenant');

        if (!$this->userService->canAddUser($tenant)) {
            return back()->withErrors([
                'limit' => "User limit reached. Your plan allows a maximum of {$tenant->max_users} users.",
            ]);
        }

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => [
                'required',
                'email',
                Rule::unique('users')->where(fn ($q) => $q->where('tenant_id', $tenant->id)),
            ],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
            'user_type' => ['nullable', 'string', 'max:50'],
            'phone'     => ['nullable', 'string', 'max:50'],
        ]);

        $this->userService->createUser($tenant, $data);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(int $id): Response
    {
        $tenant = app('tenant');
        $user   = $tenant->users()->findOrFail($id);

        // List tenant-specific roles (prefixed with "t{tenantId}_")
        $prefix = "t{$tenant->id}_";
        $roles  = Role::where('name', 'like', $prefix . '%')
            ->where('guard_name', 'web')
            ->get()
            ->map(fn ($r) => [
                'id'   => $r->id,
                'name' => str_replace($prefix, '', $r->name),
            ]);

        // Current tenant roles assigned to the user
        $userRoles = $user->roles()
            ->where('name', 'like', $prefix . '%')
            ->pluck('name')
            ->map(fn ($n) => str_replace($prefix, '', $n))
            ->toArray();

        return Inertia::render('Tenant/Users/Edit', [
            'user'      => $user,
            'roles'     => $roles,
            'userRoles' => $userRoles,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $tenant = app('tenant');
        $user   = $tenant->users()->findOrFail($id);

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => [
                'required',
                'email',
                Rule::unique('users')->where(fn ($q) => $q->where('tenant_id', $tenant->id))->ignore($user->id),
            ],
            'password'  => ['nullable', 'string', 'min:8', 'confirmed'],
            'user_type' => ['nullable', 'string', 'max:50'],
            'phone'     => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
            'roles'     => ['nullable', 'array'],
        ]);

        // Sync tenant-scoped roles if provided
        if (array_key_exists('roles', $data)) {
            $prefix       = "t{$tenant->id}_";
            $prefixedRoles = array_map(fn ($r) => $prefix . $r, $data['roles'] ?? []);
            unset($data['roles']);
            $user->syncRoles($prefixedRoles);
        }

        $this->userService->updateUser($user, $data);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $tenant = app('tenant');
        $user   = $tenant->users()->findOrFail($id);

        $this->userService->deleteUser($user);

        return redirect()->route('users.index')->with('success', 'User deleted.');
    }
}
