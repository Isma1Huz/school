<?php

declare(strict_types=1);

namespace App\Modules\Core\Jobs;

use App\Modules\Core\Jobs\base\TenantJob;
use App\Modules\Core\Mail\TenantNotificationMail;
use App\Modules\Core\Models\Notification;
use App\Modules\Core\Services\ModuleSettingsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class SendEmailJob extends TenantJob
{
    public string $queue = 'notifications';
    public int $tries = 3;
    public array $backoff = [60, 120, 300];

    public function __construct(
        string $tenantId,
        public readonly Notification $notification,
    ) {
        parent::__construct();
        $this->tenantId = $tenantId;
    }

    public function handle(ModuleSettingsService $settingsService): void
    {
        $notification = $this->notification->fresh();

        if ($notification === null) {
            return;
        }

        try {
            $fromName = $settingsService->get($this->tenantId, 'core', 'email_from_name')
                ?? $this->resolvedTenant()->name;

            $toEmail = $notification->data['email'] ?? null;

            if ($toEmail === null) {
                Log::warning('SendEmailJob: no email address in notification data', [
                    'notification_id' => $notification->id,
                ]);
                $notification->update(['status' => 'failed']);
                return;
            }

            Mail::to($toEmail)
                ->send(new TenantNotificationMail($notification, $fromName));

            $notification->update([
                'status'  => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('SendEmailJob failed', [
                'notification_id' => $notification->id,
                'tenant_id'       => $this->tenantId,
                'error'           => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->notification->update(['status' => 'failed']);

        Log::error('SendEmailJob permanently failed', [
            'notification_id' => $this->notification->id,
            'tenant_id'       => $this->tenantId,
            'error'           => $exception->getMessage(),
        ]);
    }
}
