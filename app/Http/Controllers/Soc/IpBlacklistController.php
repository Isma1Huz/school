<?php

namespace App\Http\Controllers\Soc;

use App\Http\Controllers\Controller;
use App\Models\IpBlacklist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class IpBlacklistController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ip_address' => ['required', 'ip', 'unique:ip_blacklists,ip_address'],
            'reason'     => ['nullable', 'string', 'max:1000'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        $record = IpBlacklist::create([
            'ip_address' => $data['ip_address'],
            'reason'     => $data['reason'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'blocked_by' => $request->user()->id,
            'is_active'  => true,
        ]);

        // Cache the block in Redis
        $ttl = $record->expires_at
            ? now()->diffInSeconds($record->expires_at)
            : 86400;

        Cache::put("ip:blacklist:{$record->ip_address}", true, (int) $ttl);

        return redirect()->route('soc.ip-management')
            ->with('success', "IP {$record->ip_address} has been blocked.");
    }

    public function destroy(IpBlacklist $ipBlacklist): RedirectResponse
    {
        $ipBlacklist->update(['is_active' => false]);
        Cache::forget("ip:blacklist:{$ipBlacklist->ip_address}");

        return redirect()->route('soc.ip-management')
            ->with('success', "IP {$ipBlacklist->ip_address} has been unblocked.");
    }
}
