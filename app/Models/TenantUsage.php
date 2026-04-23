<?php

declare(strict_types=1);

namespace App\Models;

use App\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Current-period counter for a metered feature within one tenant.
 *
 * Life cycle:
 *   - Row created lazily on first TrackUsageAction call for a feature.
 *   - `used` increments atomically (SELECT ... FOR UPDATE in TrackUsageAction).
 *   - When now() >= reset_at, `used` is reset to 0 and reset_at is
 *     recalculated from Feature::reset_period.
 *   - reset_at == NULL  →  lifetime counter (never resets).
 */
class TenantUsage extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'tenant_usages';

    protected $fillable = [
        'tenant_id',
        'feature_key',
        'used',
        'period_started_at',
        'reset_at',
        'last_tracked_at',
    ];

    protected $casts = [
        'used'              => 'int',
        'period_started_at' => 'datetime',
        'reset_at'          => 'datetime',
        'last_tracked_at'   => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->reset_at !== null && $this->reset_at->isPast();
    }
}
