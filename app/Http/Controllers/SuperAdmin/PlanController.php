<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    public function index(): Response
    {
        $plans = SubscriptionPlan::withCount('tenants')
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($p) => [
                'id'                      => $p->id,
                'name'                    => $p->name,
                'code'                    => $p->code,
                'description'             => $p->description,
                'applicable_tenant_types' => $p->applicable_tenant_types,
                'price_monthly'           => $p->price_monthly,
                'price_yearly'            => $p->price_yearly,
                'max_users'               => $p->max_users,
                'max_storage_mb'          => $p->max_storage_mb,
                'is_active'               => $p->is_active,
                'sort_order'              => $p->sort_order,
                'tenants_count'           => $p->tenants_count,
            ]);

        return Inertia::render('SuperAdmin/Plans/Index', compact('plans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'                    => ['required', 'string', 'max:255'],
            'code'                    => ['required', 'string', 'unique:subscription_plans,code'],
            'description'             => ['nullable', 'string'],
            'applicable_tenant_types' => ['required', 'array'],
            'price_monthly'           => ['required', 'numeric', 'min:0'],
            'price_yearly'            => ['required', 'numeric', 'min:0'],
            'max_users'               => ['nullable', 'integer', 'min:0'],
            'max_storage_mb'          => ['nullable', 'integer', 'min:0'],
            'is_active'               => ['boolean'],
            'sort_order'              => ['integer', 'min:0'],
        ]);

        SubscriptionPlan::create($data);

        return redirect()->route('super-admin.plans.index')
            ->with('success', 'Plan created successfully.');
    }

    public function update(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        $data = $request->validate([
            'name'                    => ['sometimes', 'string', 'max:255'],
            'description'             => ['nullable', 'string'],
            'applicable_tenant_types' => ['sometimes', 'array'],
            'price_monthly'           => ['sometimes', 'numeric', 'min:0'],
            'price_yearly'            => ['sometimes', 'numeric', 'min:0'],
            'max_users'               => ['nullable', 'integer', 'min:0'],
            'max_storage_mb'          => ['nullable', 'integer', 'min:0'],
            'is_active'               => ['boolean'],
            'sort_order'              => ['integer', 'min:0'],
        ]);

        $plan->update($data);

        return redirect()->route('super-admin.plans.index')
            ->with('success', 'Plan updated successfully.');
    }

    public function destroy(SubscriptionPlan $plan): RedirectResponse
    {
        $plan->delete();

        return redirect()->route('super-admin.plans.index')
            ->with('success', 'Plan deleted successfully.');
    }
}
