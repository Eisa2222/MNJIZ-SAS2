<?php

declare(strict_types=1);

/**
 * Phase C — Super Admin / Admin panel translation strings.
 *
 * Keep keys flat under feature dots (`settings.tabs.general`) so Blade
 * lookups stay readable. The Arabic counterpart lives at
 * lang/ar/admin.php with the same shape.
 */

return [
    // Phase H+ collaborative-audit fix — i18n for admin/coupons/show.blade.php.
    'coupons' => [
        'title'           => 'Coupon',
        'edit'            => 'Edit',
        'enable'          => 'Enable',
        'disable'         => 'Disable',
        'back'            => 'Back',
        'fields' => [
            'type'         => 'Type',
            'value'        => 'Value',
            'active'       => 'Active',
            'redemptions'  => 'Redemptions',
            'audit_rows'   => 'Audit rows',
            'min_amount'   => 'Min amount',
            'expires'      => 'Expires',
            'applies_to'   => 'Applies to',
        ],
        'values' => [
            'yes'             => 'YES',
            'no'              => 'NO',
            'unlimited'       => '(unlimited)',
            'any_plan'        => 'Any plan',
            'specific_plans'  => 'Specific plans',
        ],
    ],

    'settings' => [
        'title'                  => 'System Settings',
        'save'                   => 'Save changes',
        'saved'                  => 'Settings updated successfully.',
        'sensitive_hint'         => 'Leave blank to keep the existing value.',
        'test_mail'              => 'Send test email',
        'test_mail_to'           => 'Recipient email',
        'test_mail_sent'         => 'Test email sent to :to',
        'test_mail_failed'       => 'Could not send test email',
        'test_moyasar'           => 'Test Moyasar credentials',
        'test_moyasar_ok'        => 'Moyasar credentials are valid.',
        'test_moyasar_no_key'    => 'Moyasar secret key is not configured.',
        'test_moyasar_failed'    => 'Moyasar test failed (HTTP :code).',

        'tabs' => [
            'general'       => 'General',
            'trial'         => 'Trial',
            'moyasar'       => 'Moyasar',
            'mail'          => 'Mail',
            'landing'       => 'Landing',
            'notifications' => 'Notifications',
        ],

        'fields' => [
            // General
            'app_name'                     => 'Application Name',
            'app_logo'                     => 'Application Logo URL',
            'app_url'                      => 'Application URL',
            'support_email'                => 'Support Email',
            'support_phone'                => 'Support Phone',
            'default_timezone'             => 'Default Timezone',
            'default_language'             => 'Default Language',
            // Trial
            'trial_enabled'                => 'Trial Enabled',
            'trial_days'                   => 'Trial Days',
            'trial_requires_payment'       => 'Trial requires payment method',
            'trial_suspend_after_expiry'   => 'Suspend tenant after trial expiry',
            'trial_warning_days'           => 'Trial warning days (e.g. 7,3,1)',
            // Moyasar
            'moyasar_publishable_key'      => 'Moyasar Publishable Key',
            'moyasar_secret_key'           => 'Moyasar Secret Key',
            'moyasar_webhook_secret'       => 'Moyasar Webhook Secret',
            'moyasar_test_mode'            => 'Test mode',
            'moyasar_enabled_methods'      => 'Enabled payment methods',
            // Mail
            'mail_driver'                  => 'Mail Driver',
            'mail_host'                    => 'SMTP Host',
            'mail_port'                    => 'SMTP Port',
            'mail_encryption'              => 'Encryption',
            'mail_username'                => 'SMTP Username',
            'mail_password'                => 'SMTP Password',
            'mail_from_address'            => 'From Address',
            'mail_from_name'               => 'From Name',
            // Landing
            'hero_title'                   => 'Hero Title',
            'hero_subtitle'                => 'Hero Subtitle',
            'hero_cta_text'                => 'Hero CTA Label',
            'hero_cta_url'                 => 'Hero CTA URL',
            'hero_image'                   => 'Hero Image URL',
            'footer_copyright'             => 'Footer Copyright Line',
            'privacy_url'                  => 'Privacy Policy URL',
            'terms_url'                    => 'Terms of Service URL',
            // Notifications
            'notify_new_subscription'      => 'Notify on new subscription',
            'notify_payment_failed'        => 'Notify on payment failure',
            'notify_trial_expiring'        => 'Notify on trial expiring soon',
            'notify_subscription_expiring' => 'Notify on subscription expiring',
            'admin_notification_email'     => 'Notification email',
        ],
    ],
];
