<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
        }

        $user = Auth::user();

        // Super admins and SOC admins do not belong to a tenant
        if (in_array($user->user_type, ['super_admin', 'soc_admin'], true)) {
            $request->session()->regenerate();
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            return $user->user_type === 'super_admin'
                ? redirect('/super-admin')
                : redirect('/soc');
        }

        // Tenant-scoped users: enforce that the resolved tenant matches
        if (app()->has('tenant')) {
            $tenant = app('tenant');
            if ($user->tenant_id !== $tenant->id) {
                Auth::logout();
                return back()->withErrors(['email' => 'You do not have access to this account.']);
            }
        } else {
            // No tenant resolved and not a global admin — reject
            Auth::logout();
            return back()->withErrors(['email' => 'No tenant context found. Please access through your organization URL.']);
        }

        $request->session()->regenerate();

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
