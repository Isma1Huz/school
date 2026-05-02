<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $users = User::withoutGlobalScopes()
            ->with('tenant:id,name')
            ->when($request->search, fn ($q, $search) =>
                $q->where(fn ($q) =>
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                )
            )
            ->latest()
            ->paginate(20)
            ->through(fn ($u) => $this->formatUser($u));

        return Inertia::render('SuperAdmin/Users/Index', [
            'users'  => $users,
            'search' => $request->search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'unique:users,email'],
            'password'  => ['required', 'min:8'],
            'user_type' => ['required', 'in:super_admin,soc_admin,tenant_admin,staff'],
            'tenant_id' => ['nullable', 'exists:tenants,id'],
            'is_active' => ['boolean'],
        ]);

        User::withoutGlobalScopes()->create(array_filter([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'user_type' => $data['user_type'],
            'is_active' => $data['is_active'] ?? true,
        ]))->forceFill(['tenant_id' => $data['tenant_id'] ?? null])->save();

        return redirect()->route('super-admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name'      => ['sometimes', 'string', 'max:255'],
            'email'     => ['sometimes', 'email', 'unique:users,email,' . $user->id],
            'password'  => ['nullable', 'min:8'],
            'user_type' => ['sometimes', 'in:super_admin,soc_admin,tenant_admin,staff'],
            'tenant_id' => ['nullable', 'exists:tenants,id'],
            'is_active' => ['boolean'],
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // tenant_id is not in $fillable — set it via forceFill
        $tenantId = array_key_exists('tenant_id', $data) ? $data['tenant_id'] : $user->tenant_id;
        $fillableData = array_diff_key($data, ['tenant_id' => true]);

        $user->fill($fillableData)->forceFill(['tenant_id' => $tenantId])->save();

        return redirect()->route('super-admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('super-admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    private function formatUser(User $user): array
    {
        return [
            'id'            => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'user_type'     => $user->user_type,
            'is_active'     => $user->is_active,
            'last_login_at' => $user->last_login_at,
            'tenant'        => $user->tenant ? ['name' => $user->tenant->name] : null,
        ];
    }
}
