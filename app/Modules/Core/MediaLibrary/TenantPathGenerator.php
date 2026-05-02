<?php

declare(strict_types=1);

namespace App\Modules\Core\MediaLibrary;

use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

final class TenantPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return $this->basePath($media) . '/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->basePath($media) . '/conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->basePath($media) . '/responsive/';
    }

    private function basePath(Media $media): string
    {
        $tenantSlug = $this->resolveTenantSlug($media);
        $modelType  = str_replace('\\', '_', $media->model_type);
        $uuid       = $media->uuid ?? $media->id;

        return "{$tenantSlug}/{$modelType}/{$uuid}";
    }

    private function resolveTenantSlug(Media $media): string
    {
        if (!empty($media->tenant_id)) {
            $tenantId = $media->tenant_id;

            $slug = Cache::remember(
                "tenant_slug_for_media_{$tenantId}",
                300,
                static fn () => Tenant::where('id', $tenantId)->value('slug') ?? 'unknown'
            );

            return (string) $slug;
        }

        if (app()->has('tenant') && app('tenant') !== null) {
            return (string) app('tenant')->slug;
        }

        return 'unknown';
    }
}
