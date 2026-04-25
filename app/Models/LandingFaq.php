<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Phase D — CMS-driven landing-page FAQ entry.
 *
 * Central-only (no `tenant_id`). Rendered on `GET /` in the FAQ section
 * via the public LandingController.
 *
 * The `answer` column accepts limited HTML (links, bold) so an operator
 * can format payment-method or refund-policy answers richly. Sanitisation
 * happens at render time — see the landing view for the safe-list filter.
 *
 * @property int    $id
 * @property string $question
 * @property string $answer
 * @property bool   $is_active
 * @property int    $sort_order
 */
final class LandingFaq extends Model
{
    use HasFactory;

    protected $table = 'landing_faqs';

    protected $fillable = [
        'question',
        'answer',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
