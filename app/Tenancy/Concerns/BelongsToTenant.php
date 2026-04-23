<?php

declare(strict_types=1);

namespace App\Tenancy\Concerns;

use App\Models\Tenant;
use App\Tenancy\Scopes\TenantScope;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

/**
 * Attach to any Eloquent model whose rows live inside a tenant.
 *
 *   Contract:
 *     1. The table MUST have a tenant_id column (use the migration
 *        helper in database/migrations/*_add_tenant_id_to_*.php as a template).
 *     2. Every query on the model is auto-filtered by TenantContext::currentId().
 *     3. Every creating() call auto-fills tenant_id from context (or fallback).
 *     4. Mass-assigning a different tenant_id on an existing row is blocked.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model) {
            if ($model->tenant_id !== null) {
                return;
            }

            $resolved = TenantContext::currentId();

            if ($resolved === null && config('tenancy.fallback_enabled', true)) {
                $resolved = config('tenancy.default_tenant_id', 1);
            }

            if ($resolved === null) {
                throw new RuntimeException(
                    'Cannot create '.get_class($model).' without a resolved tenant. '
                    .'Set TenantContext or enable TENANCY_FALLBACK_ENABLED.'
                );
            }

            $model->tenant_id = $resolved;
        });

        static::updating(function ($model) {
            if (! $model->isDirty('tenant_id')) {
                return;
            }

            $original = $model->getOriginal('tenant_id');

            if ($original !== null && $original !== $model->tenant_id) {
                throw new RuntimeException(
                    'Cross-tenant reassignment is forbidden on '.get_class($model)
                    ." (#{$model->getKey()}): tenant_id cannot change once set."
                );
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function initializeBelongsToTenant(): void
    {
        if (! in_array('tenant_id', $this->fillable ?? [], true)) {
            $this->fillable = array_merge($this->fillable ?? [], ['tenant_id']);
        }
    }
}
