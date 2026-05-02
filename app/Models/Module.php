<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'key',
        'icon',
        'description',
        'allowed_tenant_types',
        'dependencies',
        'default_permissions',
        'settings_schema',
        'sort_order',
        'is_core',
        'is_active',
        'is_globally_disabled',
    ];

    protected function casts(): array
    {
        return [
            'allowed_tenant_types'  => 'array',
            'dependencies'          => 'array',
            'default_permissions'   => 'array',
            'settings_schema'       => 'array',
            'sort_order'            => 'integer',
            'is_core'               => 'boolean',
            'is_active'             => 'boolean',
            'is_globally_disabled'  => 'boolean',
        ];
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'module_tenant')
            ->withPivot(['is_enabled', 'settings', 'permissions_override'])
            ->withTimestamps();
    }

    public function subscriptionPlans(): BelongsToMany
    {
        return $this->belongsToMany(SubscriptionPlan::class, 'plan_modules')
            ->withPivot('is_included')
            ->withTimestamps();
    }

    public function isAvailableForTenantType(string $type): bool
    {
        return in_array($type, $this->allowed_tenant_types ?? []);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where('is_globally_disabled', false);
    }

    public function scopeForTenantType($query, string $type)
    {
        return $query->whereJsonContains('allowed_tenant_types', $type);
    }
}
