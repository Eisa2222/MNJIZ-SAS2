<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Billing\FeatureType;
use App\Enums\Billing\UsageResetPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use RuntimeException;

/**
 * Central entity. Not tenant-scoped.
 *
 * @property int $id
 * @property string $key           immutable after creation ("max_users", "legal_ai.chat" …)
 * @property string $name
 * @property FeatureType $type
 * @property UsageResetPeriod $reset_period
 * @property string|null $unit
 * @property string $group
 */
class Feature extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'type',
        'reset_period',
        'unit',
        'description',
        'group',
        'sort_order',
    ];

    protected $casts = [
        'type'         => FeatureType::class,
        'reset_period' => UsageResetPeriod::class,
        'sort_order'   => 'int',
    ];

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_features')
            ->using(PlanFeature::class)
            ->withPivot(['value', 'is_highlighted', 'sort_order'])
            ->withTimestamps();
    }

    protected static function booted(): void
    {
        static::updating(function (Feature $feature) {
            if ($feature->isDirty('key')) {
                throw new RuntimeException(
                    "Feature key is immutable (feature #{$feature->id}). "
                    ."Create a new feature and migrate usages instead."
                );
            }

            // Changing type/reset_period would invalidate historical usage rows.
            // Soft-guard: allow but warn via exception unless the feature is new.
            if ($feature->isDirty('type') && $feature->exists) {
                throw new RuntimeException(
                    "Feature type change is forbidden on existing feature #{$feature->id}. "
                    ."Create a new feature key instead."
                );
            }
        });
    }
}
