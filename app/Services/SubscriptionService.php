<?php

namespace App\Services;

use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use Carbon\Carbon;

class SubscriptionService
{
    /**
     * Assign (or change) a subscription plan for a tenant.
     */
    public function assignPlan(Tenant $tenant, SubscriptionPlan $plan, string $cycle = 'monthly'): void
    {
        $start = Carbon::today();
        $end   = $cycle === 'yearly' ? $start->copy()->addYear() : $start->copy()->addMonth();

        $tenant->update([
            'subscription_plan_id'   => $plan->id,
            'subscription_status'    => 'active',
            'subscription_start_date'=> $start->toDateString(),
            'subscription_end_date'  => $end->toDateString(),
            'billing_cycle'          => $cycle,
            'max_users'              => $plan->max_users,
            'max_storage_mb'         => $plan->max_storage_mb,
        ]);

        // Sync plan-included modules (don't disable existing add-ons)
        $moduleIds = $plan->includedModules()
            ->active()
            ->pluck('modules.id')
            ->toArray();

        foreach ($moduleIds as $moduleId) {
            $tenant->modules()->syncWithoutDetaching([
                $moduleId => ['is_enabled' => true],
            ]);
        }

        app(TenantService::class)->clearTenantCache($tenant);
    }

    /**
     * Cancel a subscription for a tenant.
     */
    public function cancelSubscription(Tenant $tenant): void
    {
        $tenant->update([
            'subscription_status' => 'cancelled',
        ]);

        app(TenantService::class)->clearTenantCache($tenant);
    }

    /**
     * Check if a tenant's subscription is still valid.
     */
    public function isValid(Tenant $tenant): bool
    {
        return $tenant->isSubscriptionValid();
    }

    /**
     * Return days remaining on the subscription (null = unlimited).
     */
    public function daysRemaining(Tenant $tenant): ?int
    {
        if ($tenant->subscription_end_date === null) {
            return null;
        }

        return (int) Carbon::today()->diffInDays($tenant->subscription_end_date, false);
    }

    /**
     * Return all active plans for a given tenant type.
     */
    public function getPlansForTenantType(string $type): \Illuminate\Support\Collection
    {
        return SubscriptionPlan::active()
            ->forTenantType($type)
            ->orderBy('sort_order')
            ->get();
    }
}
