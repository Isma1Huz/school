<?php

namespace App\Models\Traits;

use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasProfile
{
    public function profiles(): HasMany
    {
        return $this->hasMany(UserProfile::class, 'user_id');
    }

    public function profile(string $type, int $id = null): ?UserProfile
    {
        return $this->profiles()
            ->where('profileable_type', $type)
            ->when($id, fn ($q) => $q->where('profileable_id', $id))
            ->first();
    }

    public function profileData(string $type, int $id = null): array
    {
        return $this->profile($type, $id)?->data ?? [];
    }
}
