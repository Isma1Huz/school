<?php

namespace App\Http\Middleware;

use App\Models\IpBlacklist;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class IpBlacklistMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $cacheKey = "ip:blacklist:{$ip}";

        // Check Redis/cache first for performance
        $blocked = Cache::get($cacheKey);

        if ($blocked === null) {
            // Not in cache — check database
            $record = IpBlacklist::where('ip_address', $ip)
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                })
                ->first();

            if ($record) {
                $ttl = $record->expires_at
                    ? now()->diffInSeconds($record->expires_at)
                    : 86400;

                Cache::put($cacheKey, true, (int) $ttl);
                $blocked = true;
            } else {
                // Cache negative result briefly to avoid repeated DB queries
                Cache::put($cacheKey, false, 60);
                $blocked = false;
            }
        }

        if ($blocked) {
            return response()->json(['message' => 'Your IP address has been blocked.'], 403);
        }

        return $next($request);
    }
}
