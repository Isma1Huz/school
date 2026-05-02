<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    public function index(): Response
    {
        $tenants = Tenant::withTrashed()
            ->withCount('users')
            ->with('subscriptionPlan:id,name')
            ->latest()
            ->paginate(20)
            ->through(fn ($t) => $this->formatTenant($t));

        return Inertia::render('SuperAdmin/Tenants/Index', compact('tenants'));
    }

    public function show(Tenant $tenant): Response
    {
        $tenant->load('enabledModules:id,key,name');
        $tenant->loadCount('users');

        return Inertia::render('SuperAdmin/Tenants/Show', [
            'tenant' => array_merge($this->formatTenant($tenant), [
                'enabled_modules' => $tenant->enabledModules->map(fn ($m) => [
                    'id'   => $m->id,
                    'key'  => $m->key,
                    'name' => $m->name,
                ]),
                'users_count' => $tenant->users_count,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'type'                  => ['required', 'string'],
            'email'                 => ['required', 'email', 'max:255'],
            'status'                => ['required', 'in:active,inactive,suspended'],
            'subscription_plan_id'  => ['nullable', 'exists:subscription_plans,id'],
            'subscription_status'   => ['nullable', 'string'],
            'subscription_end_date' => ['nullable', 'date'],
            'max_users'             => ['nullable', 'integer', 'min:0'],
        ]);

        Tenant::create($data);

        return redirect()->route('super-admin.tenants.index')
            ->with('success', 'Tenant created successfully.');
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            'name'                  => ['sometimes', 'string', 'max:255'],
            'type'                  => ['sometimes', 'string'],
            'email'                 => ['sometimes', 'email', 'max:255'],
            'status'                => ['sometimes', 'in:active,inactive,suspended'],
            'subscription_plan_id'  => ['nullable', 'exists:subscription_plans,id'],
            'subscription_status'   => ['nullable', 'string'],
            'subscription_end_date' => ['nullable', 'date'],
            'max_users'             => ['nullable', 'integer', 'min:0'],
        ]);

        $tenant->update($data);

        return redirect()->route('super-admin.tenants.index')
            ->with('success', 'Tenant updated successfully.');
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $tenant->delete();

        return redirect()->route('super-admin.tenants.index')
            ->with('success', 'Tenant deleted successfully.');
    }

    private function formatTenant(Tenant $tenant): array
    {
        return [
            'id'                    => $tenant->id,
            'name'                  => $tenant->name,
            'slug'                  => $tenant->slug,
            'type'                  => $tenant->type,
            'status'                => $tenant->status,
            'email'                 => $tenant->email,
            'subscription_status'   => $tenant->subscription_status,
            'subscription_end_date' => $tenant->subscription_end_date,
            'max_users'             => $tenant->max_users,
            'created_at'            => $tenant->created_at,
            'plan_name'             => $tenant->subscriptionPlan?->name,
            'users_count'           => $tenant->users_count ?? null,
        ];
    }
}
