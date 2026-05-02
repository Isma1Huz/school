<?php

declare(strict_types=1);

namespace App\Modules\Core\Traits;

use App\Models\User;
use App\Modules\Core\Events\ModelCreated;
use App\Modules\Core\Events\ModelDeleted;
use App\Modules\Core\Events\ModelRestored;
use App\Modules\Core\Events\ModelUpdated;
use Illuminate\Support\Facades\Auth;

/**
 * Add this trait to any Eloquent model that should be audited.
 * It dispatches typed tenant events for created, updated, deleted,
 * and restored model lifecycle hooks.
 *
 * Safe to use inside queue workers — uses Auth::user() which returns
 * null when no session is present, and never touches session state.
 */
trait FiresTenantEvents
{
    public static function bootFiresTenantEvents(): void
    {
        static::created(static function (self $model): void {
            $tenantId = self::resolveTenantId($model);
            if ($tenantId === null) {
                return;
            }
            ModelCreated::dispatch(
                $tenantId,
                'created',
                $model,
                self::resolveUser(),
                [],
            );
        });

        static::updated(static function (self $model): void {
            $tenantId = self::resolveTenantId($model);
            if ($tenantId === null) {
                return;
            }
            ModelUpdated::dispatch(
                $tenantId,
                'updated',
                $model,
                self::resolveUser(),
                $model->getDirty(),
            );
        });

        static::deleted(static function (self $model): void {
            $tenantId = self::resolveTenantId($model);
            if ($tenantId === null) {
                return;
            }
            ModelDeleted::dispatch(
                $tenantId,
                'deleted',
                $model,
                self::resolveUser(),
                [],
            );
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(static function (self $model): void {
                $tenantId = self::resolveTenantId($model);
                if ($tenantId === null) {
                    return;
                }
                ModelRestored::dispatch(
                    $tenantId,
                    'restored',
                    $model,
                    self::resolveUser(),
                    [],
                );
            });
        }
    }

    private static function resolveTenantId(self $model): ?string
    {
        if (!empty($model->tenant_id)) {
            return (string) $model->tenant_id;
        }

        if (app()->has('tenant')) {
            $tenant = app('tenant');
            return $tenant ? (string) $tenant->id : null;
        }

        return null;
    }

    /**
     * Resolve the causer without touching session state.
     * Returns null when called from a queue worker.
     */
    private static function resolveUser(): ?User
    {
        try {
            $user = Auth::user();
            return ($user instanceof User) ? $user : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
