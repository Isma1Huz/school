<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Tenant extends Model
{
    use SoftDeletes, HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'email',
        'phone',
        'address',
        'logo',
        'favicon',
        'primary_color',
        'secondary_color',
        'settings',
        'subscription_plan_id',
        'subscription_status',
        'subscription_start_date',
        'subscription_end_date',
        'billing_cycle',
        'max_users',
        'max_storage_mb',
        'addon_modules',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'settings'              => 'array',
            'addon_modules'         => 'array',
            'subscription_start_date' => 'date',
            'subscription_end_date'   => 'date',
            'max_users'             => 'integer',
            'max_storage_mb'        => 'integer',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'module_tenant')
            ->withPivot(['is_enabled', 'settings', 'permissions_override'])
            ->withTimestamps();
    }

    public function enabledModules(): BelongsToMany
    {
        return $this->modules()->wherePivot('is_enabled', true);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSubscriptionValid(): bool
    {
        if ($this->subscription_status === 'active') {
            return $this->subscription_end_date === null || $this->subscription_end_date->isFuture();
        }

        if ($this->subscription_status === 'trial') {
            return $this->subscription_end_date === null || $this->subscription_end_date->isFuture();
        }

        return false;
    }

    public function hasModule(string $moduleKey): bool
    {
        return $this->enabledModules()
            ->where('key', $moduleKey)
            ->exists();
    }

    /**
     * Get the per-tenant settings for a specific module.
     */
    public function getModuleSettings(string $moduleKey): array
    {
        $pivot = $this->modules()
            ->where('key', $moduleKey)
            ->first()?->pivot;

        if (!$pivot) {
            return [];
        }

        $settings = $pivot->settings;

        return is_array($settings) ? $settings : (json_decode($settings, true) ?? []);
    }

    /**
     * Persist per-tenant settings for a specific module.
     */
    public function setModuleSettings(string $moduleKey, array $settings): void
    {
        $module = $this->modules()->where('key', $moduleKey)->first();

        if ($module) {
            $this->modules()->updateExistingPivot($module->id, ['settings' => $settings]);
        }
    }
}
