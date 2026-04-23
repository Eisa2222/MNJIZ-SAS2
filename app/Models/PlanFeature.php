<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Billing\FeatureType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Pivot with its own model so we can attach behavior to the mapping
 * (sorting, highlighting, value casting) without mucking with the base plan.
 */
class PlanFeature extends Pivot
{
    use HasFactory;

    protected $table = 'plan_features';

    public $incrementing = true;

    protected $fillable = [
        'plan_id',
        'feature_id',
        'value',
        'is_highlighted',
        'sort_order',
    ];

    protected $casts = [
        'is_highlighted' => 'bool',
        'sort_order'     => 'int',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    /**
     * Decoded value, cast according to the feature's type.
     *
     *   Boolean → bool
     *   Limit   → int|null   (null = unlimited)
     *   Metered → int|null   (null = unlimited, per reset_period)
     */
    public function decodedValue(FeatureType $type): bool|int|null
    {
        $raw = $this->value;

        if ($raw === null) {
            return $type === FeatureType::Boolean ? false : 0;
        }

        $unlimited = config('billing.unlimited_sentinel', '__unlimited__');

        return match ($type) {
            FeatureType::Boolean => in_array($raw, ['1', 'true', 'yes', 'on'], true),
            FeatureType::Limit,
            FeatureType::Metered => ($raw === $unlimited || $raw === '-1') ? null : (int) $raw,
        };
    }
}
