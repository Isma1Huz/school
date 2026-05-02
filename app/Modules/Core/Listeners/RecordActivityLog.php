<?php

declare(strict_types=1);

namespace App\Modules\Core\Listeners;

use App\Modules\Core\Events\TenantModelEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;

final class RecordActivityLog implements ShouldQueue
{
    use InteractsWithQueue;

    public string $connection = 'redis';
    public string $queue = 'events';
    public int $tries = 3;

    public function handle(TenantModelEvent $event): void
    {
        try {
            $model = $event->model;
            $causer = $event->causer;

            /** @var \Spatie\Activitylog\ActivityLogger $logger */
            $logger = activity()
                ->performedOn($model)
                ->withProperties([
                    'changes' => $event->changed,
                    'model_class' => get_class($model),
                    'model_id' => $model->getKey(),
                ]);

            if ($causer !== null) {
                $logger->causedBy($causer);
            }

            $logger->tap(function (Activity $activity) use ($event): void {
                $activity->tenant_id = (int) $event->tenantId;
                $activity->event = $event->action;
            })->log($event->action);
        } catch (\Throwable $e) {
            Log::error('RecordActivityLog failed', [
                'tenant_id' => $event->tenantId,
                'action'    => $event->action,
                'model'     => get_class($event->model),
                'error'     => $e->getMessage(),
            ]);

            $this->fail($e);
        }
    }

    public function failed(TenantModelEvent $event, \Throwable $exception): void
    {
        Log::error('RecordActivityLog job permanently failed', [
            'tenant_id' => $event->tenantId,
            'action'    => $event->action,
            'model'     => get_class($event->model),
            'error'     => $exception->getMessage(),
        ]);
    }
}
