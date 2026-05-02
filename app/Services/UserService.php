<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Check whether the tenant is allowed to add another user
     * given their subscription's max_users limit.
     * Returns true when max_users is 0 (unlimited).
     */
    public function canAddUser(Tenant $tenant): bool
    {
        if ($tenant->max_users === 0) {
            return true;
        }

        return $tenant->users()->count() < $tenant->max_users;
    }

    /**
     * Return a paginated list of users for the given tenant.
     */
    public function listUsers(Tenant $tenant, int $perPage = 20): LengthAwarePaginator
    {
        return User::forTenant($tenant)
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Create a new user for the tenant.
     * Throws if the subscription user limit has been reached.
     */
    public function createUser(Tenant $tenant, array $data): User
    {
        if (!$this->canAddUser($tenant)) {
            throw new \RuntimeException(
                "User limit reached. Your plan allows a maximum of {$tenant->max_users} users."
            );
        }

        return DB::transaction(function () use ($tenant, $data) {
            $user = new User(array_diff_key($data, ['password' => true]));
            $user->tenant_id = $tenant->id;
            $user->password  = Hash::make($data['password']);
            $user->save();
            return $user;
        });
    }

    /**
     * Update an existing user's details.
     */
    public function updateUser(User $user, array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return $user->fresh();
    }

    /**
     * Soft-delete a user.
     */
    public function deleteUser(User $user): void
    {
        $user->delete();
    }
}
