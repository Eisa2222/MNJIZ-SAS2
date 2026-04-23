<?php

namespace App\Helpers;

use App\Models\chat\Message;
use App\Models\Item;
use App\Models\judicial_affairs\Project;
use App\Models\OperationsCenter\Offer\Offers;
use App\Models\SystemAdministration\CredentialsManagement\ApprovalFlow;
use App\Models\SystemAdministration\CredentialsManagement\ApprovalLevel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class Helpers
{
    public static function appClasses()
    {
        $data = config('custom.custom');

        // تعيين اللغة الافتراضية للعربية إذا لم يتم تعيينها
        if (!session()->has('locale')) {
            session()->put('locale', 'ar');
            app()->setLocale('ar');
        } else {
            app()->setLocale(session()->get('locale'));
        }

        // default data array
        $DefaultData = [
            'myLayout' => 'vertical',
            'myTheme' => 'theme-default',
            'myStyle' => 'light',
            'myRTLSupport' => true,
            'myRTLMode' => true,
            'hasCustomizer' => true,
            'showDropdownOnHover' => true,
            'displayCustomizer' => true,
            'contentLayout' => 'compact',
            'headerType' => 'fixed',
            'navbarType' => 'fixed',
            'menuFixed' => true,
            'menuCollapsed' => false,
            'footerFixed' => false,
            'customizerControls' => [
                'rtl',
                'style',
                'headerType',
                'contentLayout',
                'layoutCollapsed',
                'showDropdownOnHover',
                'layoutNavbarOptions',
                'themes',
            ],
        ];

        $data = array_merge($DefaultData, $data);

        $allOptions = [
            'myLayout' => ['vertical', 'horizontal', 'blank', 'front'],
            'menuCollapsed' => [true, false],
            'hasCustomizer' => [true, false],
            'showDropdownOnHover' => [true, false],
            'displayCustomizer' => [true, false],
            'contentLayout' => ['compact', 'wide'],
            'headerType' => ['fixed', 'static'],
            'navbarType' => ['fixed', 'static', 'hidden'],
            'myStyle' => ['light', 'dark', 'system'],
            'myTheme' => ['theme-default', 'theme-bordered', 'theme-semi-dark'],
            'myRTLSupport' => [true, false],
            'myRTLMode' => [true, false],
            'menuFixed' => [true, false],
            'footerFixed' => [true, false],
            'customizerControls' => [],
        ];

        foreach ($allOptions as $key => $value) {
            if (array_key_exists($key, $DefaultData)) {
                if (gettype($DefaultData[$key]) === gettype($data[$key])) {
                    if (is_string($data[$key])) {
                        if (isset($data[$key]) && $data[$key] !== null) {
                            if (!array_key_exists($data[$key], $value)) {
                                $result = array_search($data[$key], $value, 'strict');
                                if (empty($result) && $result !== 0) {
                                    $data[$key] = $DefaultData[$key];
                                }
                            }
                        } else {
                            $data[$key] = $DefaultData[$key];
                        }
                    }
                } else {
                    $data[$key] = $DefaultData[$key];
                }
            }
        }

        $styleVal = $data['myStyle'] == "dark" ? "dark" : "light";
        $styleUpdatedVal = $data['myStyle'] == "dark" ? "dark" : $data['myStyle'];
        $layoutName = $data['myLayout'];
        $isAdmin = Str::contains($layoutName, 'front') ? false : true;

        $modeCookieName = $isAdmin ? 'admin-mode' : 'front-mode';
        $colorPrefCookieName = $isAdmin ? 'admin-colorPref' : 'front-colorPref';

        if ($layoutName !== 'blank') {
            if (isset($_COOKIE[$modeCookieName])) {
                $styleVal = $_COOKIE[$modeCookieName];
                if ($styleVal === 'system') {
                    $styleVal = isset($_COOKIE[$colorPrefCookieName]) ? $_COOKIE[$colorPrefCookieName] : 'light';
                }
                $styleUpdatedVal = $_COOKIE[$modeCookieName];
            }
        }

        isset($_COOKIE['theme']) ? $themeVal = $_COOKIE['theme'] : $themeVal = $data['myTheme'];

        $directionVal = isset($_COOKIE['direction']) ? ($_COOKIE['direction'] === "true" ? 'rtl' : 'rtl') : $data['myRTLMode'];

        if (session()->get('locale') == 'ar') {
            $layoutClasses['rtlMode'] = 'rtl';
            $layoutClasses['textDirection'] = 'rtl';
        } else {
            $layoutClasses['rtlMode'] = 'rtl';
            $layoutClasses['textDirection'] = 'rtl';
        }

        $layoutClasses = [
            'layout' => $data['myLayout'],
            'theme' => $themeVal,
            'themeOpt' => $data['myTheme'],
            'style' => $styleVal,
            'styleOpt' => $data['myStyle'],
            'styleOptVal' => $styleUpdatedVal,
            'rtlSupport' => $data['myRTLSupport'],
            'rtlMode' => $data['myRTLMode'],
            'textDirection' => $directionVal,
            'menuCollapsed' => $data['menuCollapsed'],
            'hasCustomizer' => $data['hasCustomizer'],
            'showDropdownOnHover' => $data['showDropdownOnHover'],
            'displayCustomizer' => $data['displayCustomizer'],
            'contentLayout' => $data['contentLayout'],
            'headerType' => $data['headerType'],
            'navbarType' => $data['navbarType'],
            'menuFixed' => $data['menuFixed'],
            'footerFixed' => $data['footerFixed'],
            'customizerControls' => $data['customizerControls'],
        ];

        if ($layoutClasses['menuCollapsed'] == true) {
            $layoutClasses['menuCollapsed'] = 'layout-menu-collapsed';
        }

        if ($layoutClasses['headerType'] == 'fixed') {
            $layoutClasses['headerType'] = 'layout-menu-fixed';
        }

        if ($layoutClasses['navbarType'] == 'fixed') {
            $layoutClasses['navbarType'] = 'layout-navbar-fixed';
        } elseif ($layoutClasses['navbarType'] == 'static') {
            $layoutClasses['navbarType'] = '';
        } else {
            $layoutClasses['navbarType'] = 'layout-navbar-hidden';
        }

        if ($layoutClasses['menuFixed'] == true) {
            $layoutClasses['menuFixed'] = 'layout-menu-fixed';
        }

        if ($layoutClasses['footerFixed'] == true) {
            $layoutClasses['footerFixed'] = 'layout-footer-fixed';
        }

        if ($layoutClasses['rtlSupport'] == true) {
            $layoutClasses['rtlSupport'] = '/rtl';
        }

        if ($layoutClasses['rtlMode'] == true) {
            $layoutClasses['rtlMode'] = 'rtl';
            $layoutClasses['textDirection'] = 'rtl';
        } else {
            $layoutClasses['rtlMode'] = 'rtl';
            $layoutClasses['textDirection'] = 'rtl';
        }

        if ($layoutClasses['showDropdownOnHover'] == true) {
            $layoutClasses['showDropdownOnHover'] = true;
        } else {
            $layoutClasses['showDropdownOnHover'] = false;
        }

        if ($layoutClasses['displayCustomizer'] == true) {
            $layoutClasses['displayCustomizer'] = true;
        } else {
            $layoutClasses['displayCustomizer'] = false;
        }

        return $layoutClasses;
    }

    public static function updatePageConfig($pageConfigs)
    {
        $demo = 'custom';
        if (isset($pageConfigs)) {
            if (count($pageConfigs) > 0) {
                foreach ($pageConfigs as $config => $val) {
                    Config::set('custom.' . $demo . '.' . $config, $val);
                }
            }
        }
    }




    /*
    |--------------------------------------------------------------------------
    | Approval Workflow Menu Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * التحقق من كون الموظف مُعيَّن لنوع اعتماد معين
     *
     * @param string $flowType نوع التدفق
     * @param int|null $employeeId معرف الموظف (null للمستخدم الحالي)
     * @return bool
     */
    public static function isAssignedToApprovalType(string $flowType, ?int $employeeId = null): bool
    {
        try {
            // الحصول على معرف الموظف
            $employeeId = $employeeId ?? auth()->user()?->employee?->id;

            if (!$employeeId) {
                return false;
            }

            // استخدام Eloquent مع الأعمدة الصحيحة
            $exists = ApprovalFlow::where('type', $flowType)
                ->whereHas('levels', function ($query) use ($employeeId) {
                    $query->where('employee_id', $employeeId);
                })
                ->exists();

            return $exists;
        } catch (\Exception $e) {
            \Log::error('خطأ في isAssignedToApprovalType', [
                'flow_type' => $flowType,
                'employee_id' => $employeeId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * التحقق من كون الموظف مُعيَّن لأي نوع من أنواع الاعتمادات
     *
     * @param int|null $employeeId معرف الموظف (null للمستخدم الحالي)
     * @return bool
     */
    public static function hasAnyApprovalAssignment(?int $employeeId = null): bool
    {
        try {
            // الحصول على معرف الموظف
            $employeeId = $employeeId ?? auth()->user()?->employee?->id;

            if (!$employeeId) {
                return false;
            }

            // استخدام ApprovalLevel مباشرة
            $exists = ApprovalLevel::where('employee_id', $employeeId)
                ->exists();

            return $exists;
        } catch (\Exception $e) {
            \Log::error('خطأ في hasAnyApprovalAssignment', [
                'employee_id' => $employeeId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * دالة مساعدة للتحقق من صحة البيانات (للاختبار)
     *
     * @return array
     */
    public static function debugApprovalAssignments(): array
    {
        try {
            $user = auth()->user();
            $employee = $user?->employee;
            $employeeId = $employee?->id;

            $assignments = [];

            if ($employeeId) {
                // جلب التعيينات مع الأعمدة الموجودة فقط
                $assignments = ApprovalLevel::where('employee_id', $employeeId)
                    ->with(['flow:id,type']) // فقط الأعمدة الموجودة
                    ->get()
                    ->map(function ($level) {
                        return [
                            'flow_id' => $level->flow?->id,
                            'flow_type' => $level->flow?->type,
                            'level' => $level->level,
                            'is_required' => $level->is_required ?? false,
                        ];
                    })
                    ->toArray();
            }

            // إحصائيات عامة
            $totalFlows = ApprovalFlow::count();
            $totalLevels = ApprovalLevel::count();
            $allFlowTypes = ApprovalFlow::pluck('type')->unique()->values()->toArray();

            return [
                'current_user_id' => $user?->id,
                'employee_id' => $employeeId,
                'employee_exists' => $employee ? true : false,
                'employee_name' => $employee?->name,
                'total_assignments' => count($assignments),
                'user_assignments' => $assignments,
                'all_flow_types' => $allFlowTypes,
                'total_flows_in_system' => $totalFlows,
                'total_levels_in_system' => $totalLevels,
                'flow_types_with_labels' => [
                    'contract' => 'اعتمادات العقود',
                    'offer' => 'اعتمادات العروض',
                    'leave' => 'اعتمادات الإجازات',
                    'wps' => 'اعتمادات مسيرات الرواتب',
                    'clearance_certificate' => 'اعتمادات إخلاء الطرف',
                    'advance' => 'اعتمادات السلفيات',
                    'reward' => 'اعتمادات المكافآت',
                    'deduction' => 'اعتمادات الخصومات',
                    'content' => 'اعتمادات المحتوى',
                    'custody' => 'اعتمادات العهد'
                ]
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ];
        }
    }





    /*
    |--------------------------------------------------------------------------
    | Chat Notification Helpers
    |--------------------------------------------------------------------------
    | Simple helper functions for chat notifications
    */
    public static function hasUnreadMessages(?int $userId = null): bool
    {
        try {
            return self::getUnreadMessagesCount($userId) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | الحصول على عدد الرسائل غير المقروءة للمستخدم الحالي
    |--------------------------------------------------------------------------
    */
    /*
|--------------------------------------------------------------------------
| ✅ الحصول على عدد المحادثات غير المقروءة للمستخدم الحالي
|--------------------------------------------------------------------------
*/
    public static function getUnreadMessagesCount(?int $userId = null): int
    {
        try {
            $userId = $userId ?? Auth::id();

            if (!$userId) {
                return 0;
            }
            return Message::where('receiver_id', $userId)
                ->whereNull('read_at')
                ->distinct('sender_id')
                ->count('sender_id');
        } catch (\Exception $e) {
            return 0;
        }
    }
    /*
    |--------------------------------------------------------------------------
    | تنسيق عدد الرسائل للعرض (99+ للأعداد الكبيرة)
    |--------------------------------------------------------------------------
    */
    public static function formatMessageCount(int $count): string
    {
        return $count > 99 ? '99+' : (string) $count;
    }
}
