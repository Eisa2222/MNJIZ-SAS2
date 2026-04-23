<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            // Billing catalogue (Phase 4) — plans + features. Must run BEFORE
            // tenants are created/assigned so TenantObserver finds a default plan.
            DefaultPlansSeeder::class,

            // Tenancy (Phase 2) — MUST run before anything that populates tenant-scoped tables.
            DefaultTenantSeeder::class,

            // Admin (Phase 3) — platform-level, order-independent from tenant seeders.
            DefaultAdminSeeder::class,

            PermissionsRolesSeeder::class,
            UserSeeder::class,
            SettingSeeder::class,
            RealCategoriesSeeder::class,
            // settings
            SettingsSectorsSeeder::class, //1
            SettingsStagePriceOffersSeeder::class, //2
            SettingsMarketingChannelsSeeder::class, //3
            SettingsClientStatusesSeeder::class, //4
            SettingsTemplatesSeeder::class, //5
            SettingsHrStatusesSeeder::class, //6
            SettingsProductsSeeder::class, //7
            SettingsContractStatusesSeeder::class, //8
            SettingsDepartmentContractCasesSeeder::class, //9
            TemplateVariableSeeder::class, //10

            SettingsEntityRankSeeder::class, //11
            // SettingsEntitySeeder::class, //12
            SettingsHRClassificationSeeder::class, //13
            SettingsMainCourtSeeder::class, //14
            SettingsRegionSeeder::class, //15
            SettingsCountrySeeder::class, //16
            // RoleSeeder::class, //17
            SettingsSessionTypeSeeder::class, //18
            SettingsTypeRulingsSeeder::class, //19
            // new Seeder
            SettingProjectStatusSeeder::class, //20
            ApprovalFlowsSeeder::class,

            SettingsViolationCategorySeeder::class,
            SettingsViolationSeeder::class,
            SettingsLeaveTypesSeeder::class,
            SettingsSeeder::class,


            // new Seeder
            SettingsBanksSeeder::class,

            SettingsSocial::class,

            CompanyAttachmentSeeder::class,



        ]);
    }
}
