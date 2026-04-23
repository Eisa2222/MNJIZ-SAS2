<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Central-only (never scoped by tenant). Queries typically made from the
 * Super Admin panel, cross-tenant.
 *
 * @property int $admin_id
 * @property int $tenant_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon $started_at
 * @property \Illuminate\Support\Carbon|null $ended_at
 */
class ImpersonationLog extends Model
{
    use HasFactory;

    protected $table = 'impersonation_logs';

    protected $fillable = [
        'admin_id',
        'tenant_id',
        'user_id',
        'reason',
        'ip_address',
        'user_agent',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    public function isActive(): bool
    {
        return $this->ended_at === null;
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withoutGlobalScopes();
    }
}
