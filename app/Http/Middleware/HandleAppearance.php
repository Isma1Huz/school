<?php

namespace App\Http\Middleware;

use App\Services\ModuleService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $user = $request->user();

        /** @var \App\Models\Tenant|null $tenant */
        $tenant = app()->has('tenant') ? app('tenant') : null;

        $enabledModules = [];
        if ($tenant) {
            $enabledModules = app(ModuleService::class)
                ->getEnabledModulesForTenant($tenant)
                ->pluck('key')
                ->toArray();
        }

        $sharedUser = null;
        if ($user) {
            $sharedUser = [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'user_type'     => $user->user_type,
                'avatar'        => $user->avatar,
                'phone'         => $user->phone,
                'is_active'     => $user->is_active,
                'last_login_at' => $user->last_login_at,
                'permissions'   => $user->getAllPermissions()->pluck('name')->toArray(),
                'roles'         => $user->getRoleNames()->toArray(),
            ];
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $sharedUser,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error'   => $request->session()->get('error'),
                'info'    => $request->session()->get('info'),
                'warning' => $request->session()->get('warning'),
            ],
            'enabledModules' => $enabledModules,
        ]);
    }
}
