<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

/**
 * V2 — Custom Tenant model.
 *
 * Extends Stancl's BaseTenant + adds spec-mandated columns (line 130):
 *   company_name, owner_name, owner_email, owner_phone, logo, timezone,
 *   language, status, settings (json).
 *
 * Implements TenantWithDatabase + HasDatabase trait so each tenant gets
 * its own MySQL database (multi-DB tenancy per spec line 10).
 *
 * HasDomains trait wires the polymorphic domain table for both
 * subdomain (acme.mnjiz.sa) and custom domain (acme.com) resolution
 * per spec line 11.
 */
final class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    /**
     * Stancl's BaseTenant uses a `data` JSON blob for arbitrary fields.
     * Returning the explicit list here makes them first-class columns
     * (queryable + indexable) instead of buried in JSON.
     *
     * Spec line 538: "تأكد من أن الـ Tenant Model يعرّف getCustomColumns()
     * لجميع الأعمدة المضافة"
     *
     * @return array<int, string>
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'company_name',
            'owner_name',
            'owner_email',
            'owner_phone',
            'logo',
            'timezone',
            'language',
            'status',
            'settings',
        ];
    }

    protected $casts = [
        'settings' => 'array',
        'data'     => 'array',   // stancl internal
    ];

    public const STATUS_ACTIVE    = 'active';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_PENDING   = 'pending';

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'tenant_id', 'id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'tenant_id', 'id');
    }

    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->whereIn('status', ['trialing', 'active'])
            ->latest('id')
            ->first();
    }
}
