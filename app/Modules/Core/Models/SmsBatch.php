<?php

declare(strict_types=1);

namespace App\Modules\Core\Models;

use App\Models\Tenant;
use App\Modules\Core\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SmsBatch extends Model
{
    protected $table = 'sms_batches';

    protected $fillable = [
        'message',
        'recipients',
        'recipient_count',
        'provider',
        'cost',
        'status',
        'provider_batch_id',
        'sent_at',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());
    }

    protected function casts(): array
    {
        return [
            'recipients'       => 'array',
            'recipient_count'  => 'integer',
            'cost'             => 'decimal:4',
            'sent_at'          => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
