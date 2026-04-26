<?php

declare(strict_types=1);

return [
    'title'              => 'إتمام الدفع',
    'plan_summary'       => 'ملخص الباقة',
    'billing_cycle'      => 'دورة الفوترة',
    'monthly'            => 'شهري',
    'yearly'             => 'سنوي',
    'plan'               => 'الباقة',
    'company_section'    => 'بيانات الشركة',
    'fields' => [
        'company_name' => 'اسم الشركة / المكتب',
        'owner_name'   => 'اسم المسؤول',
        'owner_email'  => 'البريد الإلكتروني',
        'owner_phone'  => 'رقم الجوال',
    ],
    'amount_section'     => 'المبلغ',
    'original_amount'    => 'السعر الأصلي',
    'discount'           => 'الخصم',
    'final_amount'       => 'المبلغ المستحق',
    'coupon' => [
        'placeholder'    => 'أدخل كود الخصم',
        'apply'          => 'تطبيق',
        'remove'         => 'إزالة',
        'code_required'  => 'الرجاء إدخال كود الكوبون.',
        'not_found'      => 'هذا الكود غير موجود.',
        'inactive'       => 'هذا الكوبون غير مُفعَّل.',
        'expired'        => 'انتهت صلاحية هذا الكوبون.',
        'exhausted'      => 'تجاوز هذا الكوبون الحد الأقصى للاستخدامات.',
        'plan_mismatch'  => 'هذا الكوبون لا ينطبق على الباقة المختارة.',
        'cycle_mismatch' => 'هذا الكوبون لا ينطبق على الدورة (:cycle).',
        'min_amount'     => 'الحد الأدنى للطلب هو :amount :currency.',
        'applied'        => 'تم تطبيق الكوبون — خصم :discount :currency.',
        'applying'       => 'جارٍ التحقق...',
    ],
    'pay'                => 'إتمام الدفع',
    'pay_methods'        => 'وسائل الدفع المقبولة',
    'sandbox_notice'     => 'هذا حساب تجريبي — لن يتم خصم أي مبلغ حقيقي.',
    'success' => [
        'title'   => 'تم الدفع بنجاح',
        'thanks'  => 'شكراً لاشتراكك في :app.',
        'next'    => 'الذهاب إلى لوحة التحكم',
    ],
    'failure' => [
        'title'   => 'تعذّر إتمام الدفع',
        'reason'  => 'لم يكتمل الدفع. لم يتم خصم أي مبلغ.',
        'try'     => 'حاول مرة أخرى',
        'support' => 'تواصل مع الدعم',
    ],
    'callback' => [
        'unpaid'  => 'حالة الدفعة لم تكتمل.',
        'invalid' => 'بيانات الدفعة غير صحيحة.',
    ],
    'cta_on_pricing'     => 'اشترك الآن',

    // Phase F — account-pending page after the new secure-onboarding flow.
    'pending' => [
        'title'           => 'تم استلام طلبك',
        'body'            => 'أرسلنا لك بريداً إلى :email يحوي رابط إعداد كلمة المرور وتفعيل حسابك.',
        'expiry_notice'   => 'الرابط صالح لمدة :hours ساعة من وقت الإرسال.',
        'home'            => 'العودة للرئيسية',
    ],
];
