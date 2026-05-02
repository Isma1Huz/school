<?php

declare(strict_types=1);

namespace App\Modules\Core\Models;

use App\Models\Tenant;
use App\Modules\Core\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

final class Notification extends Model
{
    use HasUuids;

    protected $table = 'notifications';

    protected $fillable = [
        'tenant_id',
        'notifiable_type',
        'notifiable_id',
        'type',
        'data',
        'channel',
        'status',
        'scheduled_at',
        'sent_at',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data'         => 'array',
            'scheduled_at' => 'datetime',
            'sent_at'      => 'datetime',
            'read_at'      => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeUnread($query): mixed
    {
        return $query->whereNull('read_at');
    }

    public function scopeForChannel($query, string $channel): mixed
    {
        return $query->where('channel', $channel);
    }

    public function scopePending($query): mixed
    {
        return $query->where('status', 'pending');
    }

    public function markAsRead(): void
    {
        $this->update([
            'read_at' => now(),
            'status'  => 'read',
        ]);
    }
}
