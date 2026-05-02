<?php

declare(strict_types=1);

namespace App\Modules\Core\Providers;

use App\Modules\Core\Events\ModelCreated;
use App\Modules\Core\Events\ModelDeleted;
use App\Modules\Core\Events\ModelRestored;
use App\Modules\Core\Events\ModelUpdated;
use App\Modules\Core\Events\TenantModelEvent;
use App\Modules\Core\Listeners\RecordActivityLog;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

final class CoreEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TenantModelEvent::class => [
            RecordActivityLog::class,
        ],
        ModelCreated::class => [],
        ModelUpdated::class => [],
        ModelDeleted::class => [],
        ModelRestored::class => [],
    ];

    public function boot(): void
    {
        parent::boot();
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
