<?php

declare(strict_types=1);

namespace App\Modules\Core\Services;

use App\Modules\Core\Exceptions\TenantNotBoundException;
use App\Modules\Core\Models\TenantMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class MediaService
{
    /**
     * Allowed file types per collection (mime type prefixes or exact types).
     */
    private array $collectionRules = [
        'avatars'    => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
        'documents'  => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats'],
        'logos'      => ['image/jpeg', 'image/png', 'image/svg+xml', 'image/webp'],
    ];

    /**
     * Max file sizes per collection in bytes (default: 10 MB).
     */
    private array $collectionMaxSize = [
        'avatars'   => 2 * 1024 * 1024,  // 2 MB
        'documents' => 20 * 1024 * 1024, // 20 MB
        'logos'     => 5 * 1024 * 1024,  // 5 MB
    ];

    public function uploadFile(
        Model $model,
        UploadedFile $file,
        string $collection,
        array $customProperties = [],
    ): Media {
        $this->validateFile($file, $collection);

        $tenantId = $this->resolveTenantId($model);

        $customProperties['tenant_id'] = $tenantId;

        /** @var \Spatie\MediaLibrary\HasMedia $model */
        return $model
            ->addMedia($file)
            ->withCustomProperties($customProperties)
            ->toMediaCollection($collection);
    }

    public function deleteFile(int $mediaId): void
    {
        $media = TenantMedia::withoutGlobalScopes()->findOrFail($mediaId);

        // Verify ownership before deletion (prevent cross-tenant delete)
        if (!$this->belongsToCurrentTenant($media)) {
            throw new \RuntimeException(
                "Media #{$mediaId} does not belong to the current tenant."
            );
        }

        $media->delete();
    }

    public function getFiles(Model $model, string $collection): \Spatie\MediaLibrary\MediaCollections\MediaCollection
    {
        /** @var \Spatie\MediaLibrary\HasMedia $model */
        return $model->getMedia($collection);
    }

    public function getUrl(int $mediaId, string $conversion = ''): string
    {
        $media = TenantMedia::withoutGlobalScopes()->findOrFail($mediaId);

        if (!$this->belongsToCurrentTenant($media)) {
            throw new \RuntimeException(
                "Media #{$mediaId} does not belong to the current tenant."
            );
        }

        return $conversion
            ? $media->getUrl($conversion)
            : $media->getUrl();
    }

    private function validateFile(UploadedFile $file, string $collection): void
    {
        $maxSize = $this->collectionMaxSize[$collection] ?? config('media-library.max_file_size', 10 * 1024 * 1024);

        if ($file->getSize() > $maxSize) {
            throw new \InvalidArgumentException(
                "File exceeds the maximum allowed size of " . ($maxSize / 1024 / 1024) . " MB for collection '{$collection}'."
            );
        }

        if (isset($this->collectionRules[$collection])) {
            $mime = $file->getMimeType() ?? '';
            $allowed = $this->collectionRules[$collection];
            $valid = collect($allowed)->contains(
                fn (string $allowedMime) => str_starts_with($mime, $allowedMime)
            );

            if (!$valid) {
                throw new \InvalidArgumentException(
                    "File type '{$mime}' is not allowed for collection '{$collection}'."
                );
            }
        }
    }

    private function belongsToCurrentTenant(Media $media): bool
    {
        if (!app()->has('tenant') || app('tenant') === null) {
            return true; // artisan / CLI context — allow
        }

        $currentTenantId = app('tenant')->id;

        return (int) $media->tenant_id === (int) $currentTenantId;
    }

    private function resolveTenantId(Model $model): int|string
    {
        if (!empty($model->tenant_id)) {
            return $model->tenant_id;
        }

        if (app()->has('tenant') && app('tenant') !== null) {
            return app('tenant')->id;
        }

        throw new TenantNotBoundException(
            'Cannot resolve tenant_id for media upload. ' .
            'Ensure tenant is bound or model has tenant_id set.'
        );
    }
}
