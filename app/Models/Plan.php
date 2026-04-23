<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

/**
 * Central entity. Not tenant-scoped.
 *
 * @property int $id
 * @property string $slug         immutable after creation
 * @property string $name
 * @property float $price_monthly
 * @property float $price_yearly
 * @property string $currency
 * @property int $trial_days
 * @property bool $is_active
 * @property bool $is_featured
 * @property bool $is_free
 * @property int $sort_order
 */
class Plan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_monthly',
        'price_yearly',
        'currency',
        'trial_days',
        'is_active',
        'is_featured',
        'is_free',
        'sort_order',
        'meta',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly'  => 'decimal:2',
        'trial_days'    => 'int',
        'is_active'     => 'bool',
        'is_featured'   => 'bool',
        'is_free'       => 'bool',
        'sort_order'    => 'int',
        'meta'          => 'array',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'plan_features')
            ->using(PlanFeature::class)
            ->withPivot(['value', 'is_highlighted', 'sort_order'])
            ->withTimestamps();
    }

    public function planFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    /**
     * Slug is part of the public pricing URL and referenced from env/config —
     * changing it in-place breaks links and configuration. Block on save.
     */
    protected static function booted(): void
    {
        static::updating(function (Plan $plan) {
            if ($plan->isDirty('slug')) {
                throw new RuntimeException(
                    "Plan slug is immutable (plan #{$plan->id}). Clone the plan instead."
                );
            }
        });
    }
}
