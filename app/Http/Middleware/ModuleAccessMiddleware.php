<?php

namespace App\Http\Middleware;

use App\Services\ModuleService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ModuleAccessMiddleware
{
    public function __construct(private readonly ModuleService $moduleService) {}

    public function handle(Request $request, Closure $next, string $moduleKey): Response
    {
        if (!$this->moduleService->isModuleEnabledForCurrentTenant($moduleKey)) {
            abort(403, "Module [{$moduleKey}] is not enabled for this tenant.");
        }

        return $next($request);
    }
}
