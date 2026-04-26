<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\LandingFaq;
use App\Models\LandingFeature;
use App\Models\Plan;
use App\Models\SuperAdmin;
use App\Models\SystemSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * V2 — Bootstrap seeder. Creates a working operator-ready environment:
 *
 *   - 1 default Super Admin (superadmin@mnjiz.sa / super-admin-2026)
 *   - 4 plans (Free, Basic, Pro, Enterprise) with PlanFeatures
 *   - 6 landing features
 *   - 5 landing FAQs
 *   - System settings defaults (hero text, support contact, trial knobs)
 *
 * Idempotent: safe to re-run; uses updateOrCreate / firstOrCreate.
 */
final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSuperAdmin();
        $this->seedPlans();
        $this->seedLandingContent();
        $this->seedSystemSettings();

        $this->command->info('✅ V2 seeded: SuperAdmin + plans + landing + settings.');
    }

    private function seedSuperAdmin(): void
    {
        SuperAdmin::updateOrCreate(
            ['email' => 'superadmin@mnjiz.sa'],
            [
                'name'      => 'Super Admin',
                'password'  => Hash::make('super-admin-2026'),
                'is_active' => true,
            ],
        );
    }

    private function seedPlans(): void
    {
        $plans = [
            [
                'slug' => 'free', 'name' => 'مجاني', 'sort_order' => 1,
                'description' => 'للتجربة وحده — للمحامي الفرد',
                'price_monthly' => 0, 'price_yearly' => 0,
                'trial_days' => 14, 'max_users' => 1, 'max_storage_gb' => 1,
                'is_featured' => false,
                'features' => [
                    ['feature' => 'مستخدم واحد', 'included' => true, 'limit' => '1'],
                    ['feature' => 'تخزين 1 GB', 'included' => true, 'limit' => '1 GB'],
                    ['feature' => 'دعاوى محدودة', 'included' => true, 'limit' => '5'],
                    ['feature' => 'AI القانوني', 'included' => false],
                ],
            ],
            [
                'slug' => 'starter', 'name' => 'الأساسية', 'sort_order' => 2,
                'description' => 'للمكاتب الصغيرة (حتى 5 محامين)',
                'price_monthly' => 299, 'price_yearly' => 2990,
                'trial_days' => 14, 'max_users' => 5, 'max_storage_gb' => 20,
                'is_featured' => false,
                'features' => [
                    ['feature' => 'حتى 5 مستخدمين', 'included' => true, 'limit' => '5'],
                    ['feature' => 'تخزين 20 GB', 'included' => true, 'limit' => '20 GB'],
                    ['feature' => 'دعاوى غير محدودة', 'included' => true, 'limit' => '∞'],
                    ['feature' => 'تكامل WhatsApp/SMS', 'included' => true],
                    ['feature' => 'AI القانوني', 'included' => false],
                ],
            ],
            [
                'slug' => 'professional', 'name' => 'الاحترافية', 'sort_order' => 3,
                'description' => 'للمكاتب المتوسطة — كل المميزات',
                'price_monthly' => 799, 'price_yearly' => 7990,
                'trial_days' => 14, 'max_users' => 20, 'max_storage_gb' => 100,
                'is_featured' => true, 'badge_text' => 'الأكثر شيوعاً', 'badge_color' => 'sky',
                'features' => [
                    ['feature' => 'حتى 20 مستخدم', 'included' => true, 'limit' => '20'],
                    ['feature' => 'تخزين 100 GB', 'included' => true, 'limit' => '100 GB'],
                    ['feature' => 'دعاوى غير محدودة', 'included' => true, 'limit' => '∞'],
                    ['feature' => 'AI القانوني (Chat + Drafting + Summarize)', 'included' => true],
                    ['feature' => 'تكامل Microsoft Teams', 'included' => true],
                    ['feature' => 'تكامل قيود (Qoyod)', 'included' => true],
                ],
            ],
            [
                'slug' => 'enterprise', 'name' => 'الشركات', 'sort_order' => 4,
                'description' => 'للمكاتب الكبيرة (50+ محامي)',
                'price_monthly' => 1499, 'price_yearly' => 14990,
                'trial_days' => 14, 'max_users' => null, 'max_storage_gb' => null,
                'is_featured' => false,
                'features' => [
                    ['feature' => 'مستخدمون غير محدودين', 'included' => true, 'limit' => '∞'],
                    ['feature' => 'تخزين غير محدود', 'included' => true, 'limit' => '∞'],
                    ['feature' => 'AI القانوني الكامل', 'included' => true],
                    ['feature' => 'تكاملات كاملة + API', 'included' => true],
                    ['feature' => 'مدير حساب مخصّص', 'included' => true],
                    ['feature' => 'SLA 99.9%', 'included' => true],
                ],
            ],
        ];

        foreach ($plans as $data) {
            $features = $data['features'];
            unset($data['features']);
            $plan = Plan::updateOrCreate(['slug' => $data['slug']], array_merge($data, ['is_active' => true]));
            $plan->planFeatures()->delete();
            foreach ($features as $i => $f) {
                $plan->planFeatures()->create(array_merge($f, ['sort_order' => $i]));
            }
        }
    }

    private function seedLandingContent(): void
    {
        $features = [
            ['title' => 'إدارة الدعاوى', 'description' => 'الدعاوى، الجلسات، الخصوم، المذكرات — مع تذكيرات الجلسات.', 'icon' => '⚖️'],
            ['title' => 'الموارد البشرية', 'description' => 'الموظفون، الحضور، الإجازات، الرواتب (WPS)، البصمة.', 'icon' => '👥'],
            ['title' => 'التحصيل والفوترة', 'description' => 'العقود، العروض، الدفعات، تكامل قيود (Qoyod).', 'icon' => '💰'],
            ['title' => 'ذكاء اصطناعي قانوني', 'description' => 'محادثة قانونية، صياغة المذكرات، تلخيص المستندات.', 'icon' => '🤖'],
            ['title' => 'Microsoft Teams', 'description' => 'جدولة الاجتماعات والتعاون داخل النظام.', 'icon' => '🎥'],
            ['title' => 'عزل تام', 'description' => 'كل عميل بقاعدة بيانات منفصلة. أمان من الدرجة الأولى.', 'icon' => '🔒'],
        ];
        LandingFeature::truncate();
        foreach ($features as $i => $f) {
            LandingFeature::create($f + ['sort_order' => $i, 'is_active' => true]);
        }

        $faqs = [
            ['question' => 'هل بياناتي معزولة عن باقي العملاء؟', 'answer' => 'نعم — كل عميل لديه قاعدة بيانات MySQL منفصلة. لا تسرّب، لا اختلاط.'],
            ['question' => 'هل يوجد فترة تجربة مجانية؟', 'answer' => 'نعم — 14 يوم على باقة Pro بكل المميزات، بدون بطاقة ائتمان.'],
            ['question' => 'كيف أربط حسابي بـ Microsoft Teams؟', 'answer' => 'من إعدادات الحساب → التكاملات → Microsoft → سجّل دخول بحساب Office 365.'],
            ['question' => 'هل يمكنني الترقية / التخفيض في أي وقت؟', 'answer' => 'نعم. التغييرات تنعكس على الفاتورة التالية بشكل تناسبي.'],
            ['question' => 'ماذا يحدث بعد انتهاء التجربة؟', 'answer' => 'تتلقى تذكيرات قبل الانتهاء بـ 7 و 3 و 1 يوم. إذا لم تشترك، الحساب يتوقف لكن البيانات تُحفظ 30 يوماً.'],
        ];
        LandingFaq::truncate();
        foreach ($faqs as $i => $faq) {
            LandingFaq::create($faq + ['sort_order' => $i, 'is_active' => true]);
        }
    }

    private function seedSystemSettings(): void
    {
        SystemSetting::setMany([
            // General
            'app_name'         => 'MNJIZ',
            'app_url'          => 'http://localhost:8001',
            'support_email'    => 'support@mnjiz.sa',
            'support_phone'    => '+966500000000',
            'default_timezone' => 'Asia/Riyadh',
            'default_language' => 'ar',

            // Trial
            'trial_enabled'              => '1',
            'trial_days'                 => '14',
            'trial_requires_payment'     => '0',
            'trial_suspend_after_expiry' => '1',
            'trial_warning_days'         => '3',

            // Hero / Footer
            'hero_title'        => 'منصة إدارة المكاتب القانونية',
            'hero_subtitle'     => 'نظام SaaS متكامل: دعاوى، جلسات، موارد بشرية، فوترة، AI قانوني — كل شيء في مكان واحد.',
            'hero_cta_text'     => 'ابدأ تجربتك المجانية',
            'hero_cta_url'      => '/checkout/professional',
            'footer_copyright'  => '© '.date('Y').' MNJIZ — جميع الحقوق محفوظة',
            'privacy_url'       => '#',
            'terms_url'         => '#',

            // Notifications
            'notify_new_subscription'      => '1',
            'notify_payment_failed'        => '1',
            'notify_trial_expiring'        => '1',
            'notify_subscription_expiring' => '1',
            'admin_notification_email'     => 'ops@mnjiz.sa',
        ]);
    }
}
