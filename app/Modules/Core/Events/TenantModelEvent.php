<?php

declare(strict_types=1);

namespace App\Modules\Core\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class TenantModelEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $connection = 'redis';
    public string $queue = 'events';

    public function __construct(
        public readonly string $tenantId,
        public readonly string $action,
        public readonly Model $model,
        public readonly ?User $causer = null,
        public readonly array $changed = [],
    ) {}
}
