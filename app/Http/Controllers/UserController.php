<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

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

        return Inertia::render('Tenant/Users/Edit', ['user' => $user]);
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
        ]);

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
