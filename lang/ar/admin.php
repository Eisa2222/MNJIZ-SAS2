<?php

declare(strict_types=1);

return [
    // Phase H+ collaborative-audit fix — i18n for admin/coupons/show.blade.php.
    'coupons' => [
        'title'           => 'كوبون',
        'edit'            => 'تعديل',
        'enable'          => 'تفعيل',
        'disable'         => 'تعطيل',
        'back'            => 'رجوع',
        'fields' => [
            'type'         => 'النوع',
            'value'        => 'القيمة',
            'active'       => 'مفعَّل',
            'redemptions'  => 'الاستخدامات',
            'audit_rows'   => 'سجلات التدقيق',
            'min_amount'   => 'الحد الأدنى',
            'expires'      => 'تاريخ الانتهاء',
            'applies_to'   => 'ينطبق على',
        ],
        'values' => [
            'yes'             => 'نعم',
            'no'              => 'لا',
            'unlimited'       => '(غير محدود)',
            'any_plan'        => 'جميع الباقات',
            'specific_plans'  => 'باقات محددة',
        ],
    ],

    'settings' => [
        'title'                  => 'إعدادات النظام',
        'save'                   => 'حفظ التغييرات',
        'saved'                  => 'تم حفظ الإعدادات بنجاح.',
        'sensitive_hint'         => 'اترك الحقل فارغاً للاحتفاظ بالقيمة الحالية.',
        'test_mail'              => 'إرسال بريد تجريبي',
        'test_mail_to'           => 'البريد المُستلِم',
        'test_mail_sent'         => 'تم إرسال بريد تجريبي إلى :to',
        'test_mail_failed'       => 'تعذّر إرسال البريد التجريبي',
        'test_moyasar'           => 'اختبار اعتماد Moyasar',
        'test_moyasar_ok'        => 'اعتماد Moyasar صحيح.',
        'test_moyasar_no_key'    => 'مفتاح Moyasar السرّي غير مُعيَّن.',
        'test_moyasar_failed'    => 'فشل الاختبار مع Moyasar (HTTP :code).',

        'tabs' => [
            'general'       => 'عام',
            'trial'         => 'الفترة التجريبية',
            'moyasar'       => 'Moyasar',
            'mail'          => 'البريد',
            'landing'       => 'الصفحة الترحيبية',
            'notifications' => 'الإشعارات',
        ],

        'fields' => [
            // General
            'app_name'                     => 'اسم التطبيق',
            'app_logo'                     => 'رابط شعار التطبيق',
            'app_url'                      => 'رابط التطبيق',
            'support_email'                => 'بريد الدعم الفني',
            'support_phone'                => 'هاتف الدعم الفني',
            'default_timezone'             => 'المنطقة الزمنية الافتراضية',
            'default_language'             => 'اللغة الافتراضية',
            // Trial
            'trial_enabled'                => 'تفعيل الفترة التجريبية',
            'trial_days'                   => 'أيام الفترة التجريبية',
            'trial_requires_payment'       => 'الفترة التجريبية تتطلب وسيلة دفع',
            'trial_suspend_after_expiry'   => 'إيقاف المستأجر بعد انتهاء الفترة التجريبية',
            'trial_warning_days'           => 'أيام التذكير قبل الانتهاء (مثال: 7,3,1)',
            // Moyasar
            'moyasar_publishable_key'      => 'Moyasar Publishable Key',
            'moyasar_secret_key'           => 'Moyasar Secret Key',
            'moyasar_webhook_secret'       => 'Moyasar Webhook Secret',
            'moyasar_test_mode'            => 'الوضع التجريبي',
            'moyasar_enabled_methods'      => 'وسائل الدفع المُفعَّلة',
            // Mail
            'mail_driver'                  => 'محرك البريد',
            'mail_host'                    => 'SMTP Host',
            'mail_port'                    => 'SMTP Port',
            'mail_encryption'              => 'التشفير',
            'mail_username'                => 'SMTP Username',
            'mail_password'                => 'SMTP Password',
            'mail_from_address'            => 'بريد المُرسِل',
            'mail_from_name'               => 'اسم المُرسِل',
            // Landing
            'hero_title'                   => 'عنوان الصفحة الترحيبية',
            'hero_subtitle'                => 'العنوان الفرعي',
            'hero_cta_text'                => 'نص زر الـ CTA',
            'hero_cta_url'                 => 'رابط زر الـ CTA',
            'hero_image'                   => 'رابط الصورة الرئيسية',
            'footer_copyright'             => 'حقوق الملكية في التذييل',
            'privacy_url'                  => 'رابط سياسة الخصوصية',
            'terms_url'                    => 'رابط الشروط والأحكام',
            // Notifications
            'notify_new_subscription'      => 'إشعار عند الاشتراك الجديد',
            'notify_payment_failed'        => 'إشعار عند فشل الدفع',
            'notify_trial_expiring'        => 'إشعار قرب انتهاء الفترة التجريبية',
            'notify_subscription_expiring' => 'إشعار قرب انتهاء الاشتراك',
            'admin_notification_email'     => 'بريد استقبال الإشعارات',
        ],
    ],
];
