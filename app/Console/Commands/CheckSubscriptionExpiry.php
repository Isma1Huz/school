<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\TenantService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Runs daily (scheduled in routes/console.php).
 * Suspends tenants whose subscription_end_date has passed and status is not already suspended/cancelled.
 */
final class CheckSubscriptionExpiry extends Command
{
    protected $signature   = 'subscription:check-expiry {--dry-run : Show what would be suspended without making changes}';
    protected $description = 'Suspend tenants with expired subscriptions.';

    public function handle(TenantService $tenantService): int
    {
        $today = CarbonImmutable::today();
        $dryRun = $this->option('dry-run');

        $expiredTenants = Tenant::where('subscription_status', 'active')
            ->whereNotNull('subscription_end_date')
            ->whereDate('subscription_end_date', '<', $today)
            ->get();

        if ($expiredTenants->isEmpty()) {
            $this->info('No expired subscriptions found.');
            return self::SUCCESS;
        }

        foreach ($expiredTenants as $tenant) {
            if ($dryRun) {
                $this->line("[DRY-RUN] Would suspend: {$tenant->name} (#{$tenant->id}) — expired {$tenant->subscription_end_date}");
                continue;
            }

            try {
                $tenant->update([
                    'subscription_status' => 'expired',
                    'status'              => 'suspended',
                ]);

                $tenantService->clearTenantCache($tenant);

                Log::info('Tenant suspended due to expired subscription', [
                    'tenant_id'   => $tenant->id,
                    'tenant_name' => $tenant->name,
                    'expired_at'  => $tenant->subscription_end_date,
                ]);

                $this->line("Suspended: {$tenant->name} (#{$tenant->id})");
            } catch (\Throwable $e) {
                Log::error('Failed to suspend tenant', [
                    'tenant_id' => $tenant->id,
                    'error'     => $e->getMessage(),
                ]);

                $this->error("Failed to suspend tenant #{$tenant->id}: {$e->getMessage()}");
            }
        }

        $count = $expiredTenants->count();
        $this->info($dryRun ? "Dry-run complete. {$count} tenant(s) would be suspended." : "Done. {$count} tenant(s) suspended.");

        return self::SUCCESS;
    }
}
