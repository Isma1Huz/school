<?php

declare(strict_types=1);

namespace App\Modules\Core\Http\Middleware;

use App\Models\RateLimitLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

final class TenantRateLimiter
{
    /**
     * Auth routes keyed only by IP (tenant/user not yet resolved).
     */
    private const AUTH_ROUTES = ['login', 'register', 'password.request', 'password.email', 'password.reset', 'password.update'];

    /**
     * Endpoint overrides: route name prefix → [limit per window, window in seconds].
     */
    private const ENDPOINT_OVERRIDES = [
        'api.notifications.sms.' => [5, 60],
        'api.reports.'           => [3, 60],
        'api.imports.'           => [1, 300],
    ];

    /**
     * Plan limits: code → [req/min, burst cap].
     */
    private const PLAN_LIMITS = [
        'trial'      => [30, 10],
        'standard'   => [120, 30],
        'enterprise' => [600, 100],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/*')) {
            return $this->handleApi($request, $next);
        }

        return $this->handleWeb($request, $next);
    }

    // ---------------------------------------------------------------
    // WEB — Sliding Window
    // ---------------------------------------------------------------

    private function handleWeb(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName() ?? 'unknown';

        // Auth routes: keyed by IP, 5 attempts / 15 min
        if (in_array($routeName, self::AUTH_ROUTES, true)) {
            $key    = 'rl:web:auth:' . $request->ip();
            $limit  = 5;
            $window = 900; // 15 min
        } elseif (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            // Mutation routes: 30/min per tenant+user
            $tenantId = $this->currentTenantId();
            $userId   = $request->user()?->id ?? 'guest';
            $key      = "rl:web:{$tenantId}:{$userId}:{$routeName}";
            $limit    = 30;
            $window   = 60;
        } else {
            // Read routes: no limit
            return $next($request);
        }

        $result = $this->slidingWindow($key, $limit, $window);

        if (!$result['allowed']) {
            $seconds = $result['retry_after'];
            $this->logRateLimitHit($request, 'sliding_window', $limit, 'web');

            if ($request->header('X-Inertia')) {
                return back()->withErrors(['rate_limit' => "Too many requests. Please wait {$seconds} seconds."]);
            }

            return response()->json([
                'message'     => 'Too many requests.',
                'retry_after' => $seconds,
            ], 429)->withHeaders([
                'Retry-After'       => $seconds,
                'X-RateLimit-Limit' => $limit,
                'X-RateLimit-Remaining' => 0,
            ]);
        }

        return $next($request);
    }

    // ---------------------------------------------------------------
    // API — Token Bucket
    // ---------------------------------------------------------------

    private function handleApi(Request $request, Closure $next): Response
    {
        $tenantId = $this->currentTenantId();
        $userId   = $request->user()?->id ?? 'guest';
        $routeName = $request->route()?->getName() ?? 'unknown';

        // Check endpoint overrides first
        [$overrideLimit, $overrideWindow] = $this->endpointOverride($routeName);
        if ($overrideLimit !== null) {
            $overrideKey = "rl:api:override:{$tenantId}:{$userId}:{$routeName}";
            $result = $this->slidingWindow($overrideKey, $overrideLimit, $overrideWindow);
            if (!$result['allowed']) {
                return $this->tooManyResponse($overrideLimit, $result['retry_after']);
            }
        }

        // Plan-based token bucket
        [$refillRate, $burstCap] = $this->planLimits($tenantId);
        $bucketKey = "rl:api:{$tenantId}:{$userId}";

        $result = $this->tokenBucket($bucketKey, $burstCap, $refillRate);

        if (!$result['allowed']) {
            $this->logRateLimitHit($request, 'token_bucket', $refillRate, 'api');
            return $this->tooManyResponse($refillRate, $result['retry_after']);
        }

        $response = $next($request);

        return $response->withHeaders([
            'X-RateLimit-Limit'     => $refillRate,
            'X-RateLimit-Remaining' => $result['remaining'],
        ]);
    }

    // ---------------------------------------------------------------
    // Token Bucket — atomic via Lua script (executes as single Redis command)
    // ---------------------------------------------------------------

    private function tokenBucket(string $key, int $capacity, int $refillPerMinute): array
    {
        $ttl        = (int) ceil(($capacity / $refillPerMinute) * 60 * 2);
        $refillRate = $refillPerMinute / 60.0; // tokens per second
        $nowMs      = (int) (microtime(true) * 1000);

        $tokensKey     = "{$key}:tokens";
        $lastRefillKey = "{$key}:last_refill";

        // Lua script executes atomically; no WATCH needed
        $lua = <<<'LUA'
local tokens_key      = KEYS[1]
local last_refill_key = KEYS[2]
local capacity        = tonumber(ARGV[1])
local refill_rate     = tonumber(ARGV[2])
local now_ms          = tonumber(ARGV[3])
local ttl             = tonumber(ARGV[4])

local current_tokens = tonumber(redis.call('GET', tokens_key)) or capacity
local last_refill    = tonumber(redis.call('GET', last_refill_key)) or now_ms

local elapsed   = math.max(0, (now_ms - last_refill) / 1000.0)
local new_tokens = math.min(capacity, current_tokens + elapsed * refill_rate)

if new_tokens < 1 then
    local retry_after = math.ceil((1 - new_tokens) / refill_rate)
    return {0, 0, retry_after}
end

new_tokens = new_tokens - 1

redis.call('SETEX', tokens_key,     ttl, tostring(new_tokens))
redis.call('SETEX', last_refill_key, ttl, tostring(now_ms))

return {1, math.floor(new_tokens), 0}
LUA;

        $result = Redis::connection('default')->eval(
            $lua,
            2,
            $tokensKey,
            $lastRefillKey,
            $capacity,
            $refillRate,
            $nowMs,
            $ttl,
        );

        return [
            'allowed'     => (bool) ($result[0] ?? false),
            'remaining'   => (int) ($result[1] ?? 0),
            'retry_after' => (int) ($result[2] ?? 0),
        ];
    }

    // ---------------------------------------------------------------
    // Sliding Window (for web mutations and endpoint overrides)
    // ---------------------------------------------------------------

    private function slidingWindow(string $key, int $limit, int $windowSeconds): array
    {
        $redis  = Redis::connection('default')->client();
        $nowMs  = (int) (microtime(true) * 1000);
        $cutoff = $nowMs - ($windowSeconds * 1000);
        $ttl    = $windowSeconds * 2;

        $pipe = $redis->multi(\Redis::PIPELINE);
        $pipe->zRemRangeByScore($key, '-inf', (string) $cutoff);
        $pipe->zCard($key);
        $pipe->zAdd($key, $nowMs, (string) $nowMs . ':' . uniqid('', true));
        $pipe->expire($key, $ttl);
        $results = $pipe->exec();

        $count = (int) ($results[1] ?? 0);

        if ($count >= $limit) {
            // Remove the member we just added since we're rejecting
            $redis->zRemRangeByScore($key, (string) $nowMs, '+inf');
            $oldest     = $redis->zRange($key, 0, 0, ['WITHSCORES' => true]);
            $oldestScore = !empty($oldest) ? (int) array_values($oldest)[0] : $nowMs;
            $retryAfter = (int) ceil(($oldestScore + $windowSeconds * 1000 - $nowMs) / 1000);

            return ['allowed' => false, 'remaining' => 0, 'retry_after' => max(1, $retryAfter)];
        }

        return ['allowed' => true, 'remaining' => $limit - $count - 1, 'retry_after' => 0];
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function planLimits(string|int $tenantId): array
    {
        $plan = Cache::remember(
            "tenant:{$tenantId}:plan",
            300,
            static function () use ($tenantId): string {
                $tenant = \App\Models\Tenant::with('subscriptionPlan')->find($tenantId);
                return $tenant?->subscriptionPlan?->code ?? 'trial';
            }
        );

        return self::PLAN_LIMITS[$plan] ?? self::PLAN_LIMITS['trial'];
    }

    private function endpointOverride(string $routeName): array
    {
        foreach (self::ENDPOINT_OVERRIDES as $prefix => [$limit, $window]) {
            if (str_starts_with($routeName, $prefix)) {
                return [$limit, $window];
            }
        }

        return [null, null];
    }

    private function currentTenantId(): string|int
    {
        if (app()->has('tenant') && app('tenant') !== null) {
            return app('tenant')->id;
        }

        return 'global';
    }

    private function logRateLimitHit(Request $request, string $type, int $limit, string $layer): void
    {
        try {
            $tenantId = app()->has('tenant') && app('tenant') !== null ? app('tenant')->id : null;

            RateLimitLog::create([
                'tenant_id'       => $tenantId,
                'user_id'         => $request->user()?->id,
                'ip_address'      => $request->ip(),
                'endpoint'        => $request->path(),
                'method'          => $request->method(),
                'rate_limit_type' => $type,
                'requests_count'  => 1,
                'limit_value'     => $limit,
                'blocked_at'      => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to log rate limit hit', ['error' => $e->getMessage()]);
        }
    }

    private function tooManyResponse(int $limit, int $retryAfter): Response
    {
        return response()->json([
            'message'     => 'Too many requests.',
            'retry_after' => $retryAfter,
        ], 429)->withHeaders([
            'Retry-After'           => $retryAfter,
            'X-RateLimit-Limit'     => $limit,
            'X-RateLimit-Remaining' => 0,
        ]);
    }
}
