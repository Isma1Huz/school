<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'applicable_tenant_types',
        'price_monthly',
        'price_yearly',
        'max_users',
        'max_storage_mb',
        'features',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'applicable_tenant_types' => 'array',
            'features'                => 'array',
            'price_monthly'           => 'decimal:2',
            'price_yearly'            => 'decimal:2',
            'max_users'               => 'integer',
            'max_storage_mb'          => 'integer',
            'is_active'               => 'boolean',
            'sort_order'              => 'integer',
        ];
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'plan_modules')
            ->withPivot('is_included')
            ->withTimestamps();
    }

    public function includedModules(): BelongsToMany
    {
        return $this->modules()->wherePivot('is_included', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForTenantType($query, string $type)
    {
        return $query->whereJsonContains('applicable_tenant_types', $type);
    }
}
