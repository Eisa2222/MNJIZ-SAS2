<?php

declare(strict_types=1);

return [
    // Reasons surfaced as session('subscription_block') flash banner
    // when CheckSubscription middleware redirects.
    'expired'         => 'انتهى اشتراكك. الرجاء تجديده لاستعادة الوصول الكامل.',
    'past_due'        => 'لديك دفعة متأخرة. الرجاء سدادها لمتابعة استخدام الخدمة.',
    'no_subscription' => 'لا يوجد اشتراك نشط على حسابك. الرجاء اختيار الباقة المناسبة.',

    'suspended' => [
        'page_title'   => 'الحساب موقوف',
        'heading'      => 'حسابك موقوف مؤقتاً',
        'body'         => 'تم إيقاف حسابك مؤقتاً، ولا يمكنك الوصول لخصائص النظام حالياً. للحصول على المساعدة الرجاء التواصل مع الدعم الفني، أو راجع باقات الاشتراك المتاحة لإعادة التفعيل.',
        'support_hint' => 'للمساعدة، تواصل معنا على',
    ],

    'actions' => [
        'home'           => 'العودة للرئيسية',
        'upgrade_now'    => 'مراجعة الباقات',
        'contact_support'=> 'تواصل مع الدعم',
    ],
];
