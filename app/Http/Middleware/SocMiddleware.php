<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SocMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->user_type, ['soc_admin', 'super_admin'], true)) {
            abort(403, 'Access denied. SOC access only.');
        }

        return $next($request);
    }
}
