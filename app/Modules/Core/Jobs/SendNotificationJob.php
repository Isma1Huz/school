<?php

declare(strict_types=1);

namespace App\Modules\Core\Jobs;

use App\Modules\Core\Jobs\base\TenantJob;
use App\Modules\Core\Models\Notification;
use App\Modules\Core\Notifications\Channels\SmsChannelHandler;
use Illuminate\Support\Facades\Log;

final class SendNotificationJob extends TenantJob
{
    public string $queue = 'notifications';
    public int $tries = 3;
    public array $backoff = [30, 60, 120];

    public function __construct(
        public readonly Notification $notification,
    ) {
        parent::__construct();
    }

    public function handle(SmsChannelHandler $smsHandler): void
    {
        $notification = $this->notification->fresh();

        if ($notification === null) {
            return;
        }

        try {
            match ($notification->channel) {
                'in_app' => $this->handleInApp($notification),
                'sms'    => $smsHandler->handle($notification),
                'email'  => SendEmailJob::dispatch($this->tenantId, $notification)
                                ->onQueue('notifications'),
                'push'   => $this->handlePush($notification),
                default  => throw new \InvalidArgumentException(
                    "Unknown notification channel: {$notification->channel}"
                ),
            };
        } catch (\Throwable $e) {
            Log::error('SendNotificationJob failed', [
                'notification_id' => $notification->id,
                'channel'         => $notification->channel,
                'tenant_id'       => $this->tenantId,
                'error'           => $e->getMessage(),
            ]);

            $notification->update(['status' => 'failed']);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->notification->update(['status' => 'failed']);

        Log::error('SendNotificationJob permanently failed', [
            'notification_id' => $this->notification->id,
            'channel'         => $this->notification->channel,
            'tenant_id'       => $this->tenantId,
            'error'           => $exception->getMessage(),
        ]);
    }

    private function handleInApp(Notification $notification): void
    {
        // In-app notifications are already persisted in the DB.
        // The frontend polls for unread notifications.
        $notification->update([
            'status'  => 'sent',
            'sent_at' => now(),
        ]);
    }

    private function handlePush(Notification $notification): void
    {
        // Stub: FCM / APNs push integration — log for now
        Log::info('Push notification stub', [
            'notification_id' => $notification->id,
            'data'            => $notification->data,
        ]);

        $notification->update([
            'status'  => 'sent',
            'sent_at' => now(),
        ]);
    }
}
