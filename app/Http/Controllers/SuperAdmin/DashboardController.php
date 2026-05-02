<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $stats = [
            'total_tenants'  => Tenant::count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'total_users'    => User::withoutGlobalScopes()->whereNotNull('tenant_id')->count(),
            'total_modules'  => Module::count(),
            'active_plans'   => SubscriptionPlan::where('is_active', true)->count(),
            'recent_tenants' => Tenant::latest()->limit(5)->get()->map(fn ($t) => [
                'id'         => $t->id,
                'name'       => $t->name,
                'status'     => $t->status,
                'created_at' => $t->created_at,
            ]),
        ];

        return Inertia::render('SuperAdmin/Dashboard', compact('stats'));
    }
}
