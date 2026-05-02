<?php

namespace App\Models\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasTenant
{
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeForTenant($query, int|Tenant $tenant)
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;

        return $query->where($this->qualifyColumn('tenant_id'), $tenantId);
    }

    public function scopeCurrentTenant($query)
    {
        if (app()->has('tenant')) {
            return $query->forTenant(app('tenant'));
        }

        return $query;
    }
}
