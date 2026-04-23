<?php

namespace App\Models\general_setting;

use App\Models\ElectronicServices\UserViolation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingsViolation extends Model
{
    use HasFactory;

    protected $fillable = [
        'settings_violation_category_id',
        'description',
        'penalty_first',
        'penalty_second',
        'penalty_third',
        'penalty_fourth',
        'extra_deduction',
        'user_id',

        'violation_type',
        'duration_unit',
        'duration_from',
        'duration_to',
    ];


    /**
     * العلاقة مع تصنيف المخالفة.
     */
    public function category()
    {
        return $this->belongsTo(SettingsViolationCategory::class, 'settings_violation_category_id');
    }

    /**
     * العلاقة مع سجلات المخالفات للمستخدمين.
     */
    public function userViolations()
    {
        return $this->hasMany(UserViolation::class, 'settings_violation_id');
    }


    /*
    |--------------------------------------------------------------------------
    | Appends
    |--------------------------------------------------------------------------
    */
    protected $appends = [
        'formatted_penalty_first',
        'formatted_penalty_second',
        'formatted_penalty_third',
        'formatted_penalty_fourth',
    ];

    // Accessor لتنسيق العقوبة الأولى
    public function getFormattedPenaltyFirstAttribute()
    {
        return $this->formatPenalty($this->penalty_first);
    }

    // Accessor لتنسيق العقوبة الثانية
    public function getFormattedPenaltySecondAttribute()
    {
        return $this->formatPenalty($this->penalty_second);
    }

    // Accessor لتنسيق العقوبة الثالثة
    public function getFormattedPenaltyThirdAttribute()
    {
        return $this->formatPenalty($this->penalty_third);
    }

    // Accessor لتنسيق العقوبة الرابعة
    public function getFormattedPenaltyFourthAttribute()
    {
        return $this->formatPenalty($this->penalty_fourth);
    }


    /*
    |--------------------------------------------------------------------------
    | Formate penalty
    |--------------------------------------------------------------------------
    */
    public static function formatPenalty($penalty)
    {
        if (empty($penalty)) {
            return '---';
        }

        if (strpos($penalty, ':') !== false) {
            list($type, $value) = explode(':', $penalty);

            switch ($type) {
                case 'percentage':
                    $displayText = $value . '%';
                    break;
                case 'days':
                    if ($value == 1) $displayText = 'يوم';
                    else if ($value == 2) $displayText = 'يومان';
                    else $displayText = $value . ' أيام';
                    break;
                case 'warning':
                    $displayText = 'إنذار كتابي';
                    break;
                case 'ban':
                    $displayText = 'الحرمان من الترقيات أو العلاوات لمرة واحدة';
                    break;
                case 'termination':
                    switch ($value) {
                        case 'with_benefit':
                            $displayText = 'فصل مع المكافأة';
                            break;
                        case 'without_benefit':
                            $displayText = 'فصل بدون مكافأة';
                            break;
                        case 'with_benefit_30':
                            $displayText = 'فصل من الخدمة مع المكافأة إذا لم يتجاوز الغياب (30) يوماً';
                            break;
                        case 'article_80':
                            $displayText = 'فصل من الخدمة طبقاً للمادة (الثمانون) من نظام العمل';
                            break;
                        case 'article_80_10days':
                            $displayText = 'الفصل دون مكافأة، أو تعويض، على أن يسبقه إنذار كتابي بعد الغياب مدة عشرة أيام';
                            break;
                        case 'article_80_20days':
                            $displayText = 'الفصل دون مكافأة، أو تعويض، على أن يسبقه إنذار كتابي بعد الغياب مدة عشرين يوماً';
                            break;
                        default:
                            $displayText = 'فصل من الخدمة';
                    }
                    break;
                default:
                    $displayText = $penalty;
            }

            return $displayText;
        }

        return $penalty;
    }


    /*
    |--------------------------------------------------------------------------
    | تنسيق العقوبة مع إضافة الأيقونات المناسبة
    |--------------------------------------------------------------------------
    */
    public static function formatted_penalty_with_icon($penalty)
    {
        // أولاً، قم بتنسيق العقوبة للحصول على النص المعروض
        $formattedPenalty = self::formatPenalty($penalty);

        // إذا كانت العقوبة فارغة أو ---
        if (empty($formattedPenalty) || $formattedPenalty === '---') {
            return $formattedPenalty;
        }

        // تحديد الأيقونة المناسبة بناءً على نوع العقوبة
        if (strpos($penalty, ':') !== false) {
            list($type, $value) = explode(':', $penalty);

            switch ($type) {
                case 'percentage':
                    return '<i class="ti ti-percentage me-1 text-danger"></i> ' . $formattedPenalty;

                case 'days':
                    return '<i class="ti ti-calendar-off me-1 text-danger"></i> ' . $formattedPenalty;

                case 'warning':
                    return '<i class="ti ti-alert-triangle me-1 text-warning"></i> ' . $formattedPenalty;

                case 'ban':
                    return '<i class="ti ti-ban me-1 text-danger"></i> ' . $formattedPenalty;

                case 'termination':
                    return '<i class="ti ti-user-off me-1 text-danger"></i> ' . $formattedPenalty;

                default:
                    // للأنواع غير المعروفة، استخدم الأيقونة العامة
                    return '<i class="ti ti-gavel me-1 text-danger"></i> ' . $formattedPenalty;
            }
        }

        // للعقوبات التي ليست بتنسيق نوع:قيمة، افحص النص مباشرة
        if (str_contains($formattedPenalty, '%')) {
            return '<i class="ti ti-percentage me-1 text-danger"></i> ' . $formattedPenalty;
        } elseif (str_contains($formattedPenalty, 'يوم') || str_contains($formattedPenalty, 'أيام') || str_contains($formattedPenalty, 'يومان')) {
            return '<i class="ti ti-calendar-off me-1 text-danger"></i> ' . $formattedPenalty;
        } elseif (str_contains($formattedPenalty, 'إنذار')) {
            return '<i class="ti ti-alert-triangle me-1 text-warning"></i> ' . $formattedPenalty;
        } elseif (str_contains($formattedPenalty, 'فصل') || str_contains($formattedPenalty, 'الفصل')) {
            return '<i class="ti ti-user-off me-1 text-danger"></i> ' . $formattedPenalty;
        } elseif (str_contains($formattedPenalty, 'الحرمان')) {
            return '<i class="ti ti-ban me-1 text-danger"></i> ' . $formattedPenalty;
        } else {
            // للعقوبات الأخرى غير المعروفة
            return '<i class="ti ti-gavel me-1 text-danger"></i> ' . $formattedPenalty;
        }
    }
}
