<?php

declare(strict_types=1);

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\LandingFaq;
use App\Models\LandingFeature;
use App\Models\Plan;
use App\Models\SystemSetting;
use Illuminate\Contracts\View\View;

/**
 * Phase 9 + Phase D — public marketing surface.
 *
 *   GET /        → landing page (hero + features + pricing + FAQ + footer)
 *   GET /pricing → standalone pricing comparison page
 *
 * Phase 9 shipped this controller with hardcoded copy for hero / features /
 * FAQ / footer. Phase D moves all of that into the DB:
 *
 *   - hero & footer text  → `system_settings` keys (`hero_*`, `footer_*`)
 *   - feature blocks      → `landing_features` table (CRUD via Super Admin)
 *   - FAQ entries         → `landing_faqs` table       (CRUD via Super Admin)
 *
 * The view is fully defensive — every dynamic string falls back to the
 * Phase 9 copy or a sane default if the operator hasn't set the value
 * yet, so the landing page always renders a polished page on a blank DB.
 */
final class LandingController extends Controller
{
    public function index(): View
    {
        return view('marketing.landing', [
            'plans'    => $this->sellablePlans(),
            'features' => LandingFeature::query()->active()->ordered()->get(),
            'faqs'     => LandingFaq::query()->active()->ordered()->get(),
            'hero'     => $this->heroSettings(),
            'footer'   => $this->footerSettings(),
        ]);
    }

    public function pricing(): View
    {
        return view('marketing.pricing', [
            'plans'  => $this->sellablePlans(),
            'hero'   => $this->heroSettings(),
            'footer' => $this->footerSettings(),
        ]);
    }

    /**
     * Sellable plans: active + paid (Free is offered via the trial CTA, not
     * the pricing grid). Sorted by `sort_order` so the recommended plan
     * lands in the middle column.
     */
    private function sellablePlans()
    {
        // Eager-load `features` so the pricing-grid `@foreach($plan->features)`
        // iteration in landing.blade.php and pricing.blade.php doesn't fire
        // a SELECT per plan (N+1). Catalogue is small today (~3-4 plans) but
        // this is a free win and keeps growth-safe.
        return Plan::query()
            ->with('features')
            ->where('is_active', true)
            ->where('is_free', false)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @return array{title:string,subtitle:string,cta_text:string,cta_url:string,image:?string}
     */
    private function heroSettings(): array
    {
        return [
            'title'    => (string) SystemSetting::get('hero_title',    __('landing.hero.default_title')),
            'subtitle' => (string) SystemSetting::get('hero_subtitle', __('landing.hero.default_subtitle')),
            'cta_text' => (string) SystemSetting::get('hero_cta_text', __('landing.hero.default_cta_text')),
            'cta_url'  => (string) SystemSetting::get('hero_cta_url',  '/register'),
            'image'    => SystemSetting::get('hero_image'),
        ];
    }

    /**
     * @return array{copyright:string,privacy_url:?string,terms_url:?string,support_email:?string}
     */
    private function footerSettings(): array
    {
        $year = (string) date('Y');

        return [
            'copyright'     => (string) SystemSetting::get('footer_copyright', "© {$year} MNJIZ"),
            'privacy_url'   => SystemSetting::get('privacy_url'),
            'terms_url'     => SystemSetting::get('terms_url'),
            'support_email' => SystemSetting::get('support_email', 'support@mnjiz.sa'),
        ];
    }
}
