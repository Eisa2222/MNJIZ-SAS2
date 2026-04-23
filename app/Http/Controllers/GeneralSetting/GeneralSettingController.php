<?php

namespace App\Http\Controllers\GeneralSetting;

use App\Http\Controllers\Controller;

class GeneralSettingController extends Controller
{
    public function index()
    {
        return view('general_setting.main.index');
    }

    public function system()
    {
        $cards = [];

        if (auth()->user()->can('إعدادات النظام')) {
            $cards[] =  [
                'title' => 'إعدادات النظام',
                'route' => route('general-settings.system-settings.index'),
                'icon' => 'ti ti-settings',
                'badge' => 'bg-label-primary',
                'note' => 'إدارة الإعدادات العامة والتحكم في النظام الأساسي',
            ];
        }

        if (auth()->user()->can('الصلاحيات الوظيفية')) {
            $cards[] =  [
                'title' => 'الصلاحيات الوظيفية',
                'route' => route('roles.index'),
                'icon' => 'ti ti-shield-lock',
                'badge' => 'bg-label-warning',
                'note' => 'تحديد الأدوار والصلاحيات للموظفين والمستخدمين',
            ];
        }

        if (auth()->user()->can('إدارة الإعتمادات')) {
            $cards[] = [
                'title' => 'إدارة الإعتمادات',
                'route' => route('approval-flows.index'),
                'icon' => 'ti ti-certificate',
                'badge' => 'bg-label-info',
                'note' => 'إدارة وتتبع الاعتمادات و مستوياتها',
            ];
        }


        if (auth()->user()->can('سجل النشاطات')) {
            $cards[] = [
                'title' => 'سجل النشاطات',
                'route' => route('activity_logs.index'),
                'icon' => 'ti ti-activity',
                'badge' => 'bg-label-success',
                'note' => 'عرض وتتبع جميع العمليات والأنشطة في النظام',
            ];
        }


        if (auth()->user()->can('سجل الرسائل')) {
            $cards[] = [
                'title' => 'سجل الرسائل',
                'route' => route('messageLogs.index'),
                'icon' => 'ti ti-mail-opened',
                'badge' => 'bg-label-secondary',
                'note' => 'إدارة ومتابعة الرسائل والإشعارات المرسلة',
            ];
        }


        if (auth()->user()->can('النماذج')) {
            $cards[] = [
                'title' => 'إعدادات النماذج',
                'route' => route('settings-templates.index'),
                'icon' => 'ti ti-template',
                'badge' => 'bg-label-success',
                'note' => 'ضبط قوالب النماذج وتخصيص الوثائق',
            ];
        }

        return view('general_setting.main.system', compact('cards'));
    }

    public function operations()
    {
        $cards = [];

        if (auth()->user()->can('اقسام المشاريع والقضايا')) {
            $cards[] =  [
                'title' => 'أقسام المشاريع والقضايا',
                'route' => route('settings-departments-contracts.index'),
                'icon' => 'ti ti-folders',
                'badge' => 'bg-label-primary',
                'note' => 'إدارة الهيكل التنظيمي للأقسام',
            ];
        }

        if (auth()->user()->can('حالات العقود')) {
            $cards[] =  [
                'title' => 'حالات العقود والمشاريع',
                'route' => route('settings-contract-statuses.index'),
                'icon' => 'ti ti-clipboard-list',
                'badge' => 'bg-label-success',
                'note' => 'ضبط مراحل وحالات العقود',
            ];
        }

        if (auth()->user()->can('المنتجات')) {
            $cards[] =  [
                'title' => 'المنتجات',
                'route' => route('settings-products.index'),
                'icon' => 'ti ti-package',
                'badge' => 'bg-label-warning',
                'note' => 'إضافة وتعديل منتجات الشركة',
            ];
        }

        if (auth()->user()->can('حالات العميل')) {
            $cards[] =  [
                'title' => 'حالات العميل',
                'route' => route('settings-client-status.index'),
                'icon' => 'ti ti-user-check',
                'badge' => 'bg-label-info',
                'note' => 'تعريف حالة تقدّم العميل',
            ];
        }

        if (auth()->user()->can('قنوات التسويق')) {
            $cards[] =  [
                'title' => 'قنوات التسويق',
                'route' => route('settings-marketing-channel.index'),
                'icon' => 'ti ti-share-3',
                'badge' => 'bg-label-danger',
                'note' => 'إدارة القنوات التسويقية',
            ];
        }

        if (auth()->user()->can('القطاعات')) {
            $cards[] =  [
                'title' => 'القطاعات',
                'route' => route('settings-sector.index'),
                'icon' => 'ti ti-building',
                'badge' => 'bg-label-secondary',
                'note' => 'تصنيف القطاعات ',
            ];
        }

        if (auth()->user()->can('المدن')) {
            $cards[] =  [
                'title' => 'المدن',
                'route' => route('settings-region.index'),
                'icon' => 'ti ti-map-2',
                'badge' => 'bg-label-primary',
                'note' => 'قائمة المدن المتاحة',
            ];
        }

        if (auth()->user()->can('قائمة الدول')) {
            $cards[] =  [
                'title' => 'قائمة الدول',
                'route' => route('settings-country.index'),
                'icon' => 'ti ti-world',
                'badge' => 'bg-label-success',
                'note' => 'إدارة الدول في النظام',
            ];
        }

        if (auth()->user()->can('مواقع التواصل')) {
            $cards[] =  [
                'title' => 'مواقع التواصل',
                'route' => route('settings-social.index'),
                'icon' => 'ti ti-brand-facebook',
                'badge' => 'bg-label-warning',
                'note' => 'روابط منصات التواصل الرسمية',
            ];
        }

        if (auth()->user()->can('قائمة البنوك')) {
            $cards[] =  [
                'title' => 'قائمة البنوك',
                'route' => route('settings-banks.index'),
                'icon' => 'ti ti-building-bank',
                'badge' => 'bg-label-info',
                'note' => 'البنوك المعتمدة ',
            ];
        }

        return view('general_setting.main.operations', compact('cards'));
    }

    public function hr()
    {
        $cards = [];

        if (auth()->user()->can('حالات الموارد البشرية')) {
            $cards[] =  [
                'title' => 'حالات الموارد البشرية',
                'route' => route('settings-hr-statuses.index'),
                'icon' => 'ti ti-user-check',
                'badge' => 'bg-label-primary',
                'note' => 'تعريف حالة الموظف',
            ];
        }


        if (auth()->user()->can('تصنيف الموارد البشرية')) {
            $cards[] =  [
                'title' => 'تصنيف الموارد البشرية',
                'route' => route('settings-hr_classification.index'),
                'icon' => 'ti ti-users',
                'badge' => 'bg-label-success',
                'note' => 'تصنيفات الموظفين',
            ];
        }


        if (auth()->user()->can('تصنيف المخالفات')) {
            $cards[] =  [
                'title' => 'تصنيف المخالفات',
                'route' => route('settings-violation-categories.index'),
                'icon' => 'ti ti-flag',
                'badge' => 'bg-label-warning',
                'note' => 'أقسام المخالفات',
            ];
        }


        if (auth()->user()->can('أنواع المخالفات')) {
            $cards[] =  [
                'title' => 'أنواع المخالفات',
                'route' => route('settings-violations.index'),
                'icon' => 'ti ti-alert-triangle',
                'badge' => 'bg-label-danger',
                'note' => 'تفصيل المخالفات',
            ];
        }


        if (auth()->user()->can('أنواع الإجازات')) {
            $cards[] =  [
                'title' => 'أنواع الإجازات',
                'route' => route('settings-leave-types.index'),
                'icon' => 'ti ti-calendar-time',
                'badge' => 'bg-label-info',
                'note' => 'تعريف أنواع الإجازات',
            ];
        }


        if (auth()->user()->can('تصنيفات الاصول')) {
            $cards[] =  [
                'title' => 'تصنيفات الأصول',
                'route' => route('settings-asset-category.index'),
                'icon' => 'ti ti-box',
                'badge' => 'bg-label-secondary',
                'note' => 'تصنيف أصول الشركة',
            ];
        }


        if (auth()->user()->can('مرجعية الاصول')) {
            $cards[] =  [
                'title' => 'مرجعية الاصول',
                'route' => route('settings-storage-location.index'),
                'icon' => 'ti ti-map-pin',
                'badge' => 'bg-label-primary',
                'note' => 'مواقع تخزين الأصول',
            ];
        }


        return view('general_setting.main.hr', compact('cards'));
    }

    public function marketing()
    {
        $cards = [];

        if (auth()->user()->can('أنواع الحملات والمحتوى')) {
            $cards[] =  [
                'title' => 'أنواع الحملات والمحتوى',
                'route' => route('settings-content-types.index'),
                'icon' => 'ti ti-speakerphone',
                'badge' => 'bg-label-primary',
                'note' => 'تصنيف الحملات والمحتوى',
            ];
        }


        if (auth()->user()->can('أنماط النشر')) {
            $cards[] =  [
                'title' => 'أنماط النشر',
                'route' => route('settings-publishing-patterns.index'),
                'icon' => 'ti ti-layout-kanban',
                'badge' => 'bg-label-success',
                'note' => 'طرق/أوقات النشر',
            ];
        }


        if (auth()->user()->can('أهداف الحملات والمحتوى')) {
            $cards[] =  [
                'title' => 'أهداف الحملات والمحتوى',
                'route' => route('settings-content-purpose.index'),
                'icon' => 'ti ti-target',
                'badge' => 'bg-label-warning',
                'note' => 'الغرض التسويقي',
            ];
        }


        if (auth()->user()->can('أقسام الحملات')) {
            $cards[] =  [
                'title' => 'أقسام الحملات',
                'route' => route('settings-campaign-sections.index'),
                'icon' => 'ti ti-separator-horizontal',
                'badge' => 'bg-label-danger',
                'note' => 'تقسيم الحملات',
            ];
        }


        if (auth()->user()->can('الجمهور المستهدف')) {
            $cards[] =  [
                'title' => 'الجمهور المستهدف',
                'route' => route('settings-target-audience.index'),
                'icon' => 'ti ti-users',
                'badge' => 'bg-label-info',
                'note' => 'تحديد الجمهور',
            ];
        }

        return view('general_setting.main.marketing', compact('cards'));
    }

    public function legal()
    {
        $cards = [];

        if (auth()->user()->can('المحكمة')) {
            $cards[] =  [
                'title' => 'المحاكم',
                'route' => route('settings-main-court.index'),
                'icon' => 'ti ti-building-bank',
                'badge' => 'bg-label-primary',
                'note' => 'إدارة المحاكم',
            ];
        }

        if (auth()->user()->can('درجة الجهة')) {
            $cards[] =  [
                'title' => 'درجة الجهة',
                'route' => route('settings-entity_rank.index'),
                'icon' => 'ti ti-stairs-up',
                'badge' => 'bg-label-success',
                'note' => 'تصنيف درجات الجهات',
            ];
        }

        if (auth()->user()->can('التصنيفات الرئيسية')) {
            $cards[] =  [
                'title' => 'التصنيفات الرئيسية',
                'route' => route('settings-category.index'),
                'icon' => 'ti ti-category',
                'badge' => 'bg-label-warning',
                'note' => 'الفئات الرئيسية',
            ];
        }

        if (auth()->user()->can('التصنيفات الفرعية')) {
            $cards[] =  [
                'title' => 'التصنيفات الفرعية',
                'route' => route('settings-subcategory.index'),
                'icon' => 'ti ti-category-2',
                'badge' => 'bg-label-danger',
                'note' => 'الفئات الفرعية',
            ];
        }

        if (auth()->user()->can('أنواع الدعاوى')) {
            $cards[] =  [
                'title' => 'أنواع الدعاوى',
                'route' => route('settings-lawsuits-types.index'),
                'icon' => 'ti ti-scale',
                'badge' => 'bg-label-info',
                'note' => 'تعريف الدعاوى',
            ];
        }

        if (auth()->user()->can('أنواع الجلسات')) {
            $cards[] =  [
                'title' => 'أنواع الجلسات',
                'route' => route('settings-session-type.index'),
                'icon' => 'ti ti-clock',
                'badge' => 'bg-label-secondary',
                'note' => 'ضبط الجلسات',
            ];
        }

        if (auth()->user()->can('أنواع الاحكام')) {
            $cards[] =  [
                'title' => 'أنواع الأحكام',
                'route' => route('settings-rule-type.index'),
                'icon' => 'ti ti-file-certificate',
                'badge' => 'bg-label-primary',
                'note' => 'تفاصيل الأحكام',
            ];
        }

        return view('general_setting.main.legal', compact('cards'));
    }

    public function purchasing()
    {
        $cards = [];
        if (auth()->user()->can('تصنيف المشتريات')) {
            $cards[] =  [
                'title' => 'تصنيف المشتريات',
                'route' => route('settings-purchase-category.index'),
                'icon' => 'ti ti-shopping-cart',
                'badge' => 'bg-label-primary',
                'note' => 'ضبط فئات المشتريات',
            ];
        }
        return view('general_setting.main.purchasing', compact('cards'));
    }
}
