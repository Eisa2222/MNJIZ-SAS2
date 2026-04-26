<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * V2 — Subscription plan. Central DB.
 *
 * Spec line 145 — accessors for formatted price + yearly savings.
 */
final class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description',
        'price_monthly', 'price_yearly', 'currency',
        'trial_days', 'max_users', 'max_storage_gb',
        'features', 'limits',
        'is_active', 'is_featured', 'sort_order',
        'badge_text', 'badge_color',
    ];

    protected $casts = [
        'price_monthly'   => 'decimal:2',
        'price_yearly'    => 'decimal:2',
        'trial_days'      => 'integer',
        'max_users'       => 'integer',
        'max_storage_gb'  => 'integer',
        'features'        => 'array',
        'limits'          => 'array',
        'is_active'       => 'boolean',
        'is_featured'     => 'boolean',
        'sort_order'      => 'integer',
    ];

    public function planFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class)->orderBy('sort_order');
    }

    /** Spec ref: scope used by LandingController@index. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function getFormattedMonthlyPriceAttribute(): string
    {
        return number_format((float) $this->price_monthly, 0).' '.$this->currency;
    }

    public function getFormattedYearlyPriceAttribute(): string
    {
        return number_format((float) $this->price_yearly, 0).' '.$this->currency;
    }

    /**
     * Yearly savings %. Returns 0 when monthly price is 0 to avoid
     * division-by-zero on Free plans.
     */
    public function getYearlySavingsPercentAttribute(): int
    {
        $monthlyAnnual = (float) $this->price_monthly * 12;
        if ($monthlyAnnual <= 0) {
            return 0;
        }
        $saving = $monthlyAnnual - (float) $this->price_yearly;
        return (int) round(($saving / $monthlyAnnual) * 100);
    }
}
