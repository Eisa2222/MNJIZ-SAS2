<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Phase A — central-only mapping of HTTP host → tenant.
 *
 * Used by `InitializeTenantByDomainOrSubdomain` middleware to resolve which
 * tenant owns the incoming request. NEVER scoped by `BelongsToTenant` /
 * `TenantScope` — this is a central index that must be readable BEFORE the
 * tenant context is known.
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $domain
 * @property bool $is_primary
 * @property \Illuminate\Support\Carbon|null $verified_at
 */
class Domain extends Model
{
    use HasFactory;

    protected $table = 'domains';

    protected $fillable = [
        'tenant_id',
        'domain',
        'is_primary',
        'verified_at',
    ];

    protected $casts = [
        'is_primary'  => 'bool',
        'verified_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /**
     * Lower-cased host comparison helper. HTTP hosts are case-insensitive but
     * we store them lower-cased for stable indexing.
     */
    public static function normalize(string $host): string
    {
        return mb_strtolower(trim($host));
    }
}
