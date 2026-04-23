<?php

declare(strict_types=1);

namespace App\Tenancy\Scopes;

use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use RuntimeException;

/**
 * Applied by the BelongsToTenant trait. Forces every query on a tenant-aware
 * model to be scoped by the currently-resolved tenant_id — even when the
 * developer forgets. This is the single most important defense against
 * cross-tenant data leakage.
 */
final class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = TenantContext::currentId();

        if ($tenantId === null) {
            if (config('tenancy.strict', false)) {
                throw new RuntimeException(
                    'Strict tenancy mode is on and no tenant is resolved while querying '
                    .get_class($model).'. Wrap the call in TenantContext::runAs() or '
                    .'run it inside a request that passed through InitializeTenantMiddleware.'
                );
            }

            // Non-strict: allow migrations, seeders, and console commands to work.
            return;
        }

        $builder->where($model->getTable().'.tenant_id', $tenantId);
    }

    /**
     * Allow developers to opt-out explicitly: Model::withoutTenancy()->...
     * Registered as a macro in BelongsToTenant::bootBelongsToTenant().
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('withoutTenancy', function (Builder $builder) {
            return $builder->withoutGlobalScope(self::class);
        });
    }
}
