<?php

declare(strict_types=1);

namespace App\Models;

use App\Tenancy\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Central-only entity. NEVER scoped by TenantScope. NEVER extends BelongsToTenant.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $domain
 * @property string $status active|suspended
 * @property int|null $plan_id
 */
class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_ACTIVE    = 'active';
    public const STATUS_SUSPENDED = 'suspended';

    protected $table = 'tenants';

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'status',
        'plan_id',
        'meta',
    ];

    protected $casts = [
        'meta'    => 'array',
        'plan_id' => 'int',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Inverse-direction relation. ALWAYS bypasses TenantScope because this
     * relation IS the scope — the belongsTo side (`$tenant->users()`) is
     * already filtered by `users.tenant_id = $tenant->id` via the foreign
     * key. Layering the global scope on top would demand the CURRENT request
     * context match $tenant — which is exactly what the Super Admin needs
     * to violate in order to see a non-default tenant's users.
     *
     * SoftDeletes and other scopes stay in place — only TenantScope is removed.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class)
            ->withoutGlobalScope(TenantScope::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(TenantUsage::class)
            ->withoutGlobalScope(TenantScope::class);
    }

    public function hasPlan(): bool
    {
        return $this->plan_id !== null;
    }

    /**
     * Convenience helper — delegates to FeatureGate. Use this from Blade:
     *
     *     @if ($tenant->hasFeature('legal_ai'))
     *
     * Pass through a feature key; returns bool. Limit / metered checks should
     * go through CheckFeatureLimitAction to get quantitative data.
     */
    public function hasFeature(string $featureKey): bool
    {
        return app(\App\Services\Billing\FeatureGate::class)
            ->hasAccess($this->id, $featureKey);
    }
}
