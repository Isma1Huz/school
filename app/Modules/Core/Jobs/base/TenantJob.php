<?php

declare(strict_types=1);

namespace App\Modules\Core\Jobs\base;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Abstract base class for all tenant-aware jobs in the system.
 *
 * Concrete jobs must extend this class. Only the tenant ID is serialized
 * (not the full model) to keep the queue payload lean and avoid stale data.
 */
abstract class TenantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $tenantId;

    public function __construct()
    {
        if (app()->has('tenant') && app('tenant') !== null) {
            $this->tenantId = (string) app('tenant')->id;
        }
    }

    /**
     * Re-fetch the tenant from the DB to avoid working with stale data.
     */
    protected function resolvedTenant(): Tenant
    {
        $tenant = Tenant::find($this->tenantId);

        if ($tenant === null) {
            throw new \RuntimeException("Tenant #{$this->tenantId} not found.");
        }

        return $tenant;
    }
}
