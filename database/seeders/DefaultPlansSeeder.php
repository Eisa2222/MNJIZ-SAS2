<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Billing\FeatureType;
use App\Enums\Billing\UsageResetPeriod;
use App\Models\Feature;
use App\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * Seeds a realistic 3-tier plan catalog aligned with MNJIZ's actual domains:
 *
 *   Free        — sole practitioner trial
 *   Starter     — small firm (up to ~5 lawyers)
 *   Professional — mid-size firm, AI + Qoyod sync
 *
 * Idempotent: safe to run multiple times. Existing plans are updated; missing
 * plan_features rows are created; extra rows on old plans are NOT deleted
 * (manual cleanup via Super Admin).
 */
class DefaultPlansSeeder extends Seeder
{
    public function run(): void
    {
        $features = $this->seedFeatures();
        $this->seedPlans($features);
    }

    /** @return array<string, Feature> */
    private function seedFeatures(): array
    {
        $catalog = [
            // ----- Hard limits ---------------------------------------------------
            ['max_users',            'Team Members',              FeatureType::Limit,   UsageResetPeriod::Never,   'users',      'core',    10],
            ['max_clients',          'Clients',                   FeatureType::Limit,   UsageResetPeriod::Never,   'clients',    'core',    20],
            ['max_lawsuits',         'Lawsuits',                  FeatureType::Limit,   UsageResetPeriod::Never,   'lawsuits',   'core',    30],
            ['max_storage_mb',       'Storage (MB)',              FeatureType::Limit,   UsageResetPeriod::Never,   'MB',         'core',    40],

            // ----- Boolean ("available yes/no") ----------------------------------
            ['legal_ai.chat',        'Legal AI Chat',             FeatureType::Boolean, UsageResetPeriod::Never,   null,         'ai',      50],
            ['legal_ai.drafting',    'AI-Assisted Drafting',      FeatureType::Boolean, UsageResetPeriod::Never,   null,         'ai',      51],
            ['qoyod.sync',           'Qoyod Accounting Sync',     FeatureType::Boolean, UsageResetPeriod::Never,   null,         'integrations', 60],
            ['microsoft.teams',      'Microsoft Teams Meetings',  FeatureType::Boolean, UsageResetPeriod::Never,   null,         'integrations', 61],
            ['biostation.device',    'BioStation Integration',    FeatureType::Boolean, UsageResetPeriod::Never,   null,         'integrations', 62],
            ['api.access',           'Public API Access',         FeatureType::Boolean, UsageResetPeriod::Never,   null,         'api',     70],

            // ----- Metered (roll over each period) -------------------------------
            ['legal_ai.calls',       'AI Calls per Month',        FeatureType::Metered, UsageResetPeriod::Monthly, 'calls',      'ai',      80],
            ['sms.messages',         'SMS per Month',             FeatureType::Metered, UsageResetPeriod::Monthly, 'messages',   'sms',     90],
            ['api.requests',         'API Requests per Month',    FeatureType::Metered, UsageResetPeriod::Monthly, 'requests',   'api',     91],
        ];

        $out = [];

        foreach ($catalog as [$key, $name, $type, $reset, $unit, $group, $sort]) {
            /** @var Feature $f */
            $f = Feature::query()->firstOrCreate(
                ['key' => $key],
                [
                    'name'         => $name,
                    'type'         => $type,
                    'reset_period' => $reset,
                    'unit'         => $unit,
                    'group'        => $group,
                    'sort_order'   => $sort,
                ]
            );

            $out[$key] = $f;
        }

        return $out;
    }

    /** @param array<string, Feature> $features */
    private function seedPlans(array $features): void
    {
        $unlimited = config('billing.unlimited_sentinel', '__unlimited__');

        $definitions = [
            // slug     monthly yearly free?  features-map
            ['free', [
                'name'          => 'Free',
                'description'   => 'Try MNJIZ with a single lawyer and basic case management.',
                'price_monthly' => 0,
                'price_yearly'  => 0,
                'is_free'       => true,
                'is_featured'   => false,
                'trial_days'    => 0,
                'sort_order'    => 10,
                'features' => [
                    'max_users'         => '1',
                    'max_clients'       => '10',
                    'max_lawsuits'      => '5',
                    'max_storage_mb'    => '500',
                    'legal_ai.chat'     => '0',
                    'legal_ai.drafting' => '0',
                    'qoyod.sync'        => '0',
                    'microsoft.teams'   => '0',
                    'biostation.device' => '0',
                    'api.access'        => '0',
                    'legal_ai.calls'    => '0',
                    'sms.messages'      => '0',
                    'api.requests'      => '0',
                ],
            ]],
            ['starter', [
                'name'          => 'Starter',
                'description'   => 'For small firms. AI chat, Microsoft Teams, Qoyod sync.',
                'price_monthly' => 299,
                'price_yearly'  => 2990,
                'is_free'       => false,
                'is_featured'   => true,
                'trial_days'    => 14,
                'sort_order'    => 20,
                'features' => [
                    'max_users'         => '10',
                    'max_clients'       => '500',
                    'max_lawsuits'      => '500',
                    'max_storage_mb'    => '20000',
                    'legal_ai.chat'     => '1',
                    'legal_ai.drafting' => '0',
                    'qoyod.sync'        => '1',
                    'microsoft.teams'   => '1',
                    'biostation.device' => '0',
                    'api.access'        => '0',
                    'legal_ai.calls'    => '500',
                    'sms.messages'      => '500',
                    'api.requests'      => '0',
                ],
            ]],
            ['professional', [
                'name'          => 'Professional',
                'description'   => 'For mid-to-large firms. Unlimited AI, BioStation, API.',
                'price_monthly' => 799,
                'price_yearly'  => 7990,
                'is_free'       => false,
                'is_featured'   => false,
                'trial_days'    => 14,
                'sort_order'    => 30,
                'features' => [
                    'max_users'         => $unlimited,
                    'max_clients'       => $unlimited,
                    'max_lawsuits'      => $unlimited,
                    'max_storage_mb'    => '200000',
                    'legal_ai.chat'     => '1',
                    'legal_ai.drafting' => '1',
                    'qoyod.sync'        => '1',
                    'microsoft.teams'   => '1',
                    'biostation.device' => '1',
                    'api.access'        => '1',
                    'legal_ai.calls'    => $unlimited,
                    'sms.messages'      => '5000',
                    'api.requests'      => '100000',
                ],
            ]],
        ];

        foreach ($definitions as [$slug, $attrs]) {
            $featureMap = $attrs['features'];
            unset($attrs['features']);

            /** @var Plan $plan */
            $plan = Plan::query()->updateOrCreate(
                ['slug' => $slug],
                array_merge($attrs, [
                    'currency'  => config('billing.currency', 'SAR'),
                    'is_active' => true,
                ])
            );

            foreach ($featureMap as $key => $value) {
                if (! isset($features[$key])) {
                    continue;
                }

                $plan->features()->syncWithoutDetaching([
                    $features[$key]->id => [
                        'value'          => $value,
                        'is_highlighted' => in_array($key, ['max_users', 'legal_ai.chat', 'qoyod.sync'], true),
                    ],
                ]);
            }
        }
    }
}
