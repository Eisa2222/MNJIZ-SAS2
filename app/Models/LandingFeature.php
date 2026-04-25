<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Phase D — CMS-driven landing-page Feature block.
 *
 * Central-only model (no `tenant_id`, no BelongsToTenant trait). Rows are
 * curated by Super Admins and rendered by the public LandingController on
 * `GET /`.
 *
 * Cache: writes call `forgetLandingCache()` so the next public request
 * picks up the change immediately. Read path is light (≤30 rows in
 * practice) so we don't memoise yet.
 *
 * @property int    $id
 * @property string $title
 * @property string $description
 * @property string|null $icon
 * @property string|null $image
 * @property bool   $is_active
 * @property int    $sort_order
 */
final class LandingFeature extends Model
{
    use HasFactory;

    protected $table = 'landing_features';

    protected $fillable = [
        'title',
        'description',
        'icon',
        'image',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope: only rows the operator has flagged for public display.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: canonical render order (sort_order asc, then id asc as a
     * deterministic tiebreaker for rows the operator hasn't ranked yet).
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
