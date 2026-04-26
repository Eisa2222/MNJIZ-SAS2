<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\LandingFaq;
use App\Models\LandingFeature;
use App\Models\Plan;
use App\Models\SystemSetting;
use Illuminate\View\View;

/**
 * V2 — Landing page. Spec lines 158-179.
 * All content driven from Central DB (plans, landing_features,
 * landing_faqs, system_settings).
 */
final class LandingController extends Controller
{
    public function index(): View
    {
        return view('landing.index', [
            'plans'    => Plan::active()->with('planFeatures')->get(),
            'features' => LandingFeature::active()->get(),
            'faqs'     => LandingFaq::active()->get(),
            'settings' => $this->settingsBag(),
        ]);
    }

    /**
     * Spec line 162: a flat array of the keys the landing view needs.
     */
    private function settingsBag(): array
    {
        return [
            'app_name'       => SystemSetting::get('app_name', 'MNJIZ'),
            'app_logo'       => SystemSetting::get('app_logo'),
            'hero_title'     => SystemSetting::get('hero_title', 'منصة إدارة المكاتب القانونية'),
            'hero_subtitle'  => SystemSetting::get('hero_subtitle', 'نظام SaaS متكامل للمكاتب القانونية والموارد البشرية في السعودية'),
            'hero_cta_text'  => SystemSetting::get('hero_cta_text', 'ابدأ الآن'),
            'hero_cta_url'   => SystemSetting::get('hero_cta_url', '/register'),
            'hero_image'     => SystemSetting::get('hero_image'),
            'support_email'  => SystemSetting::get('support_email', 'support@mnjiz.sa'),
            'support_phone'  => SystemSetting::get('support_phone'),
            'privacy_url'    => SystemSetting::get('privacy_url', '#'),
            'terms_url'      => SystemSetting::get('terms_url', '#'),
            'footer_copyright' => SystemSetting::get('footer_copyright', '© '.date('Y').' MNJIZ'),
        ];
    }
}
