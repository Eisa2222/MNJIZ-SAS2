<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Platform super-admin. Central-only: NEVER uses BelongsToTenant.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $role       super_admin|admin|support
 * @property string $status     active|suspended
 * @property \Illuminate\Support\Carbon|null $last_login_at
 */
class Admin extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN       = 'admin';
    public const ROLE_SUPPORT     = 'support';

    public const STATUS_ACTIVE    = 'active';
    public const STATUS_SUSPENDED = 'suspended';

    protected $table = 'admins';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'password'          => 'hashed',
    ];

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function canImpersonate(): bool
    {
        return $this->isActive() && in_array($this->role, [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_SUPPORT,
        ], true);
    }
}
