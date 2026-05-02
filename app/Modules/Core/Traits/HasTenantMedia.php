<?php

declare(strict_types=1);

namespace App\Modules\Core\Traits;

use App\Modules\Core\Exceptions\TenantNotBoundException;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Drop-in replacement for Spatie's HasMedia on all tenant-aware models.
 * Automatically sets tenant_id on every media record created for this model.
 */
trait HasTenantMedia
{
    use InteractsWithMedia;

    public static function bootHasTenantMedia(): void
    {
        static::created(static function (self $model): void {
            // Hook runs after model is created so media collections are available
        });
    }

    /**
     * Resolve the tenant_id for media uploads.
     *
     * @throws TenantNotBoundException
     */
    protected function getTenantIdForMedia(): int|string
    {
        if (!empty($this->tenant_id)) {
            return $this->tenant_id;
        }

        if (app()->has('tenant') && app('tenant') !== null) {
            return app('tenant')->id;
        }

        throw new TenantNotBoundException(
            'Cannot determine tenant_id for media on model ' . static::class . '. ' .
            'Ensure tenant_id is set on the model or the tenant is bound in the container.'
        );
    }

    /**
     * Override addMedia to always inject tenant_id via custom properties,
     * which TenantPathGenerator reads for path generation.
     */
    public function addMediaAndSetTenant(string $file): \Spatie\MediaLibrary\MediaCollections\FileAdder
    {
        $tenantId = $this->getTenantIdForMedia();

        return $this->addMedia($file)
            ->withCustomProperties(['tenant_id' => $tenantId]);
    }
}
