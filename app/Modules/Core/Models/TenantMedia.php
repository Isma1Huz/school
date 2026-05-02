<?php

declare(strict_types=1);

namespace App\Modules\Core\Models;

use App\Modules\Core\Scopes\TenantMediaScope;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TenantMedia extends Media
{
    protected static function booted(): void
    {
        parent::booted();

        static::addGlobalScope(new TenantMediaScope());
    }
}
