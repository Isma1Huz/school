<?php

namespace App\Models;

use App\Models\Traits\HasModulePermissions;
use App\Models\Traits\HasProfile;
use App\Models\Traits\HasTenant;
use App\Modules\Core\Scopes\TenantScope;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasRoles;
    use HasTenant, HasProfile, HasModulePermissions;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'user_type',
        'preferences',
        'settings',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
            'preferences'       => 'array',
            'settings'          => 'array',
            'is_active'         => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Only apply TenantScope when a tenant is actually bound so that
        // super-admin queries (tenant_id = null) are not incorrectly scoped.
        static::addGlobalScope(new TenantScope());
    }
}
