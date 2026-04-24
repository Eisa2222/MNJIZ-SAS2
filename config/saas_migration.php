<?php

declare(strict_types=1);

/**
 * Phase 7 — data-migration configuration registry.
 *
 * Centralized, auditable definitions consumed by the
 * `saas:migration:*` Artisan commands. Adjust these lists
 * before running a new migration phase.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Central tables — NEVER carry tenant_id
    |--------------------------------------------------------------------------
    | These tables are platform-wide. Inventory + validate commands skip
    | them when looking for nullable tenant_id (even if the column was
    | added accidentally). Any table listed here is treated as shared.
    */
    'central_tables' => [
        'tenants',
        'admins',
        'plans',
        'features',
        'plan_features',
        'coupons',
        'central_settings',
        'saas_migration_runs',
        'migrations',
        'failed_jobs',
        'jobs',
        'password_reset_tokens',
        'personal_access_tokens',
        'permissions',
        'telescope_entries',
        'telescope_entries_tags',
        'telescope_monitoring',
    ],

    /*
    |--------------------------------------------------------------------------
    | Lookup / reference tables
    |--------------------------------------------------------------------------
    | Shared across tenants (e.g. country list, HR classifications). They
    | do NOT need tenant_id; their content is seeded once and consumed
    | globally. Skipped by backfill.
    */
    'lookup_tables' => [
        'settings_categories',
        'settings_subcategories',
        'settings_banks',
        'settings_client_statuses',
        'settings_contract_statuses',
        'settings_countries',
        'settings_department_contract_cases',
        'settings_entity_ranks',
        'settings_entities',
        'settings_hr_statuses',
        'settings_h_r_classifications',
        'settings_lawsuits_types',
        'settings_leave_types',
        'settings_main_courts',
        'settings_marketing_channels',
        'settings_products',
        'settings_project_statues',
        'settings_purchase_categories',
        'settings_regions',
        'settings_sectors',
        'settings_session_types',
        'settings_socials',
        'settings_stage_price_offers',
        'settings_templates',
        'settings_type_rulings',
        'settings_violations',
        'settings_violation_categories',
        'settings_content_types',
        'settings_content_purposes',
        'settings_content_pillars',
        'settings_campaign_sections',
        'settings_publishing_patterns',
        'settings_target_audiences',
        'settings_asset_categories',
        'settings_storage_locations',
        'settings_display_locations',
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy singleton `settings` columns → tenant_settings key map
    |--------------------------------------------------------------------------
    | When running `saas:migration:migrate-settings`, each column listed
    | here is read off the legacy Settings row and written to a scoped
    | `tenant_settings` entry for the same tenant.
    |
    | `is_encrypted` flags sensitive values — those are encrypted at rest
    | by the TenantSetting model's value mutator.
    |
    | The legacy `settings` row is NOT deleted (rule: no hard-delete
    | before production cutover).
    */
    'settings_map' => [
        // ─── Microsoft Graph / Azure AD ──────────────────────────────
        'microsoft_client_id'      => ['group' => 'microsoft', 'is_encrypted' => false, 'cast' => 'string'],
        'microsoft_client_secret'  => ['group' => 'microsoft', 'is_encrypted' => true,  'cast' => 'string'],
        'microsoft_redirect_uri'   => ['group' => 'microsoft', 'is_encrypted' => false, 'cast' => 'string'],
        'microsoft_tenant_id'      => ['group' => 'microsoft', 'is_encrypted' => true,  'cast' => 'string'],
        'main_email'               => ['group' => 'microsoft', 'is_encrypted' => false, 'cast' => 'string'],

        // ─── SMS (4jawaly) ───────────────────────────────────────────
        'sms_provider'        => ['group' => 'sms', 'is_encrypted' => false, 'cast' => 'string'],
        'sms_api_key'         => ['group' => 'sms', 'is_encrypted' => true,  'cast' => 'string'],
        'sms_api_secret'      => ['group' => 'sms', 'is_encrypted' => true,  'cast' => 'string'],
        'sms_api_endpoint'    => ['group' => 'sms', 'is_encrypted' => false, 'cast' => 'string'],
        'sms_sender_id'       => ['group' => 'sms', 'is_encrypted' => false, 'cast' => 'string'],
        'sms_from_number'     => ['group' => 'sms', 'is_encrypted' => false, 'cast' => 'string'],
        'sms_callback_url'    => ['group' => 'sms', 'is_encrypted' => false, 'cast' => 'string'],
        'sms_enabled'         => ['group' => 'sms', 'is_encrypted' => false, 'cast' => 'bool'],

        // ─── WhatsApp / Twilio ───────────────────────────────────────
        'whatsapp_enabled'       => ['group' => 'whatsapp', 'is_encrypted' => false, 'cast' => 'bool'],
        'twilio_account_sid'     => ['group' => 'whatsapp', 'is_encrypted' => true,  'cast' => 'string'],
        'twilio_auth_token'      => ['group' => 'whatsapp', 'is_encrypted' => true,  'cast' => 'string'],
        'twilio_whatsapp_from'   => ['group' => 'whatsapp', 'is_encrypted' => false, 'cast' => 'string'],

        // ─── BioStation ──────────────────────────────────────────────
        'biostation_api_key'         => ['group' => 'biostation', 'is_encrypted' => true,  'cast' => 'string'],
        'biostation_api_url'         => ['group' => 'biostation', 'is_encrypted' => false, 'cast' => 'string'],
        'biostation_device_ip'       => ['group' => 'biostation', 'is_encrypted' => false, 'cast' => 'string'],
        'biostation_device_port'     => ['group' => 'biostation', 'is_encrypted' => false, 'cast' => 'int'],
        'biostation_timezone'        => ['group' => 'biostation', 'is_encrypted' => false, 'cast' => 'string'],
        'biostation_device_name'     => ['group' => 'biostation', 'is_encrypted' => false, 'cast' => 'string'],
        'biostation_sync_interval'   => ['group' => 'biostation', 'is_encrypted' => false, 'cast' => 'int'],

        // ─── AI / Qoyod ──────────────────────────────────────────────
        'openai_api_key'   => ['group' => 'ai',     'is_encrypted' => true,  'cast' => 'string'],
        'openai_model'     => ['group' => 'ai',     'is_encrypted' => false, 'cast' => 'string'],
        'chatgpt_enabled'  => ['group' => 'ai',     'is_encrypted' => false, 'cast' => 'bool'],
        'qoyod_api_key'    => ['group' => 'qoyod',  'is_encrypted' => true,  'cast' => 'string'],
        'qoyod_base_url'   => ['group' => 'qoyod',  'is_encrypted' => false, 'cast' => 'string'],

        // ─── Branding / System ───────────────────────────────────────
        'office_name'        => ['group' => 'branding', 'is_encrypted' => false, 'cast' => 'string'],
        'colors'             => ['group' => 'branding', 'is_encrypted' => false, 'cast' => 'json'],
        'logo_text'          => ['group' => 'branding', 'is_encrypted' => false, 'cast' => 'string'],
        'image'              => ['group' => 'branding', 'is_encrypted' => false, 'cast' => 'string'],
        'template_image'     => ['group' => 'branding', 'is_encrypted' => false, 'cast' => 'string'],
        'signature'          => ['group' => 'branding', 'is_encrypted' => false, 'cast' => 'string'],

        // ─── Working hours / Attendance ──────────────────────────────
        'work_start_time'             => ['group' => 'attendance', 'is_encrypted' => false, 'cast' => 'string'],
        'work_end_time'               => ['group' => 'attendance', 'is_encrypted' => false, 'cast' => 'string'],
        'attendance_start_time'       => ['group' => 'attendance', 'is_encrypted' => false, 'cast' => 'string'],
        'attendance_end_time'         => ['group' => 'attendance', 'is_encrypted' => false, 'cast' => 'string'],
        'departure_start_time'        => ['group' => 'attendance', 'is_encrypted' => false, 'cast' => 'string'],
        'attendance_system_locked'    => ['group' => 'attendance', 'is_encrypted' => false, 'cast' => 'bool'],
        'manual_attendance_enabled'   => ['group' => 'attendance', 'is_encrypted' => false, 'cast' => 'bool'],
        'company_latitude'            => ['group' => 'attendance', 'is_encrypted' => false, 'cast' => 'string'],
        'company_longitude'           => ['group' => 'attendance', 'is_encrypted' => false, 'cast' => 'string'],
        'daily_working_hours'         => ['group' => 'attendance', 'is_encrypted' => false, 'cast' => 'int'],
        'weekly_days_off'             => ['group' => 'attendance', 'is_encrypted' => false, 'cast' => 'json'],
        'payroll_disbursement_day'    => ['group' => 'attendance', 'is_encrypted' => false, 'cast' => 'int'],

        // ─── HR / System ─────────────────────────────────────────────
        'insurance_deduction'         => ['group' => 'hr', 'is_encrypted' => false, 'cast' => 'bool'],
        'insurance_percentage'        => ['group' => 'hr', 'is_encrypted' => false, 'cast' => 'string'],
        'archive_delete_duration'     => ['group' => 'system', 'is_encrypted' => false, 'cast' => 'int'],
        'maintenance_mode'            => ['group' => 'system', 'is_encrypted' => false, 'cast' => 'bool'],
        'support_emails'              => ['group' => 'system', 'is_encrypted' => false, 'cast' => 'json'],
    ],

    /*
    |--------------------------------------------------------------------------
    | File column registry — drives `saas:migration:migrate-files`
    |--------------------------------------------------------------------------
    | Each entry is [table, column, subdir-under-tenant-root]. The migration
    | command moves files from their current storage location (legacy paths
    | like `attachments/x.pdf` or `uploads/sessions/y.jpg`) to
    |   storage/app/public/tenants/{tenant_id}/{subdir}/{basename}
    | and updates the DB column to the new relative path.
    */
    'file_columns' => [
        // Lawsuit attachments
        ['table' => 'lawsuit_attachments',   'column' => 'file_path',              'subdir' => 'legal-affair/lawsuits/attachments'],
        ['table' => 'lawsuits',              'column' => 'decision_attachment',    'subdir' => 'legal-affair/lawsuits/attachments'],
        ['table' => 'lawsuits',              'column' => 'request_attachment',     'subdir' => 'legal-affair/lawsuits/attachments'],
        ['table' => 'lawsuits',              'column' => 'defense_memo_attachment','subdir' => 'legal-affair/lawsuits/attachments'],
        ['table' => 'lawsuits',              'column' => 'judgment_attachment',    'subdir' => 'legal-affair/lawsuits/attachments'],
        ['table' => 'lawsuits',              'column' => 'lawsuit_attachment',     'subdir' => 'legal-affair/lawsuits/attachments'],

        // Sessions
        ['table' => 'sessions',              'column' => 'session_control_attached','subdir' => 'legal-affair/sessions/control'],
        ['table' => 'sessions',              'column' => 'rule_attached',           'subdir' => 'legal-affair/sessions/rules'],

        // Power of attorney
        ['table' => 'power_of_attorneys',    'column' => 'file_attachment',         'subdir' => 'legal-affair/power-of-attorneys'],

        // Memos
        ['table' => 'memos',                 'column' => 'attachment',              'subdir' => 'legal-affair/memos'],

        // Contract attachments
        ['table' => 'contract_attachments',  'column' => 'file_path',               'subdir' => 'operations/contracts/attachments'],

        // Task attachments
        ['table' => 'task_attachments',      'column' => 'file_path',               'subdir' => 'tasks/attachments'],

        // Employee profile
        ['table' => 'employees',             'column' => 'profile_picture',         'subdir' => 'hr/employees/photos'],
        ['table' => 'employees',             'column' => 'resume',                  'subdir' => 'hr/employees/resumes'],
        ['table' => 'employees',             'column' => 'qualification_certificate','subdir' => 'hr/employees/qualifications'],
        ['table' => 'employees',             'column' => 'contract_attachment',     'subdir' => 'hr/employees/contracts'],
        ['table' => 'employees',             'column' => 'id_attachment',           'subdir' => 'hr/employees/ids'],
        ['table' => 'employees',             'column' => 'bank_account_attachment', 'subdir' => 'hr/employees/bank'],
        ['table' => 'employees',             'column' => 'national_address_attachment','subdir' => 'hr/employees/address'],
        ['table' => 'employees',             'column' => 'signature',               'subdir' => 'hr/employees/signatures'],
        ['table' => 'employees',             'column' => 'background_image',        'subdir' => 'hr/employees/backgrounds'],
        ['table' => 'employees',             'column' => 'business_card',           'subdir' => 'hr/employees/cards'],

        // Chat attachments
        ['table' => 'messages',              'column' => 'attachment_path',         'subdir' => 'chat/attachments'],

        // AI
        ['table' => 'ai_chat_messages',      'column' => 'file_path',               'subdir' => 'ai/chat/uploads'],

        // Project / Judicial
        ['table' => 'project_attachments',   'column' => 'attachment_file',         'subdir' => 'judicial/projects/attachments'],
        ['table' => 'documents',             'column' => 'file_path',               'subdir' => 'judicial/documents'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default tenant identification
    |--------------------------------------------------------------------------
    */
    'default_tenant_slug' => env('TENANCY_DEFAULT_TENANT_SLUG', 'default'),
    'default_tenant_id'   => env('TENANCY_DEFAULT_TENANT_ID', 1),
];
