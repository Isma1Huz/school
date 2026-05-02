<?php

declare(strict_types=1);

namespace App\Modules\Core\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (!app()->has('tenant')) {
            return;
        }

        $tenant = app('tenant');

        if ($tenant === null) {
            return;
        }

        $builder->where($model->qualifyColumn('tenant_id'), $tenant->id);
    }
}
