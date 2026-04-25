<?php

declare(strict_types=1);

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Contracts\View\View;

/**
 * Phase 9 — public marketing surface.
 *
 *   GET /        → landing page (hero + features + pricing + FAQ)
 *   GET /pricing → standalone pricing comparison page
 *
 * Both pages pull plans live from the DB so changing the catalog (or
 * launching a promotional plan) does not require a code change. We only
 * surface plans flagged `is_active=true` and exclude `is_free` from the
 * primary pricing grid (the trial is the upsell into Pro).
 */
final class LandingController extends Controller
{
    public function index(): View
    {
        return view('marketing.landing', [
            'plans' => $this->sellablePlans(),
        ]);
    }

    public function pricing(): View
    {
        return view('marketing.pricing', [
            'plans' => $this->sellablePlans(),
        ]);
    }

    /**
     * Sellable plans: active + paid (Free is offered via the trial CTA, not
     * the pricing grid). Sorted by `sort_order` so the recommended plan
     * lands in the middle column.
     */
    private function sellablePlans()
    {
        return Plan::query()
            ->where('is_active', true)
            ->where('is_free', false)
            ->orderBy('sort_order')
            ->get();
    }
}
