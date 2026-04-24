<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Audit record for one run of a `saas:migration:*` Artisan command.
 * Central table — not scoped to any tenant.
 */
class SaasMigrationRun extends Model
{
    use HasFactory;

    protected $table = 'saas_migration_runs';

    protected $fillable = [
        'command',
        'mode',
        'status',
        'started_at',
        'finished_at',
        'summary',
        'errors',
        'created_by',
    ];

    protected $casts = [
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
        'summary'     => 'array',
        'errors'      => 'array',
    ];
}
