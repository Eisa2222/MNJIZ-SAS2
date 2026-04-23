<?php

namespace App\Enums\LegalAffair\Session\SessionCompletion;


enum SummaryReportStatus: string
{
    case RaisedForConsideration = 'raised_for_consideration';    // مرفوعة للنظر
    case None                   = 'none';                       // لا يوجد
    case CaseStrikingOff        = 'case_striking_off';          // شطب الدعوى
    case Settlement             = 'settlement';                 // الصلح
    case StayOfProceedings      = 'stay_of_proceedings';        // وقف السير
    case Postponement           = 'postponement';               // تأجيل
    case CasePreservation       = 'case_preservation';          // حفظ الدعوى
    case SubstantiveRuling      = 'substantive_ruling';         // حكم موضوعي
    case FormalRuling           = 'formal_ruling';              // حكم شكلي
    case RulingAffirmation      = 'ruling_affirmation';         // تأييد الحكم

    public function label(): string
    {
        return match ($this) {
            self::RaisedForConsideration => 'مرفوعة للنظر',
            self::None                   => 'لا يوجد',
            self::CaseStrikingOff        => 'شطب الدعوى',
            self::Settlement             => 'الصلح',
            self::StayOfProceedings      => 'وقف السير',
            self::Postponement           => 'تأجيل',
            self::CasePreservation       => 'حفظ الدعوى',
            self::SubstantiveRuling      => 'حكم موضوعي',
            self::FormalRuling           => 'حكم شكلي',
            self::RulingAffirmation      => 'تأييد الحكم',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::RaisedForConsideration => 'info',
            self::None                   => 'secondary',
            self::CaseStrikingOff        => 'danger',
            self::Settlement             => 'success',
            self::StayOfProceedings      => 'warning',
            self::Postponement           => 'primary',
            self::CasePreservation       => 'dark',
            self::SubstantiveRuling      => 'success',
            self::FormalRuling           => 'info',
            self::RulingAffirmation      => 'success',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::RaisedForConsideration => 'ti-arrow-up-circle',
            self::None                   => 'ti-circle-dashed',
            self::CaseStrikingOff        => 'ti-circle-x',
            self::Settlement             => 'ti-handshake',
            self::StayOfProceedings      => 'ti-player-pause',
            self::Postponement           => 'ti-clock',
            self::CasePreservation       => 'ti-archive',
            self::SubstantiveRuling      => 'ti-gavel',
            self::FormalRuling           => 'ti-file-certificate',
            self::RulingAffirmation      => 'ti-check-circle',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn(self $status) => [
                'id' => $status->value,
                'name' => $status->label(),
                'color' => $status->color(),
                'icon' => $status->icon()
            ],
            self::cases()
        );
    }

    public static function values(): array
    {
        return array_map(fn(self $status) => $status->value, self::cases());
    }

    /**
     * تحديد ما إذا كانت الحالة تتطلب تاريخ آخر مهلة للاعتراض
     */
    public function requiresObjectionDeadline(): bool
    {
        return in_array($this, [
            self::SubstantiveRuling,
            self::FormalRuling,
        ], true);
    }

    /**
     * تحديد ما إذا كانت الحالة تؤدي إلى إغلاق الدعوى
     */
    public function shouldCloseLawsuit(): bool
    {
        return in_array($this, [
            self::CaseStrikingOff,
            self::Settlement,
            self::CasePreservation,
        ], true);
    }

    /**
     * تحديد ما إذا كانت الحالة تبقي الجلسة نشطة
     */
    public function keepsSessionActive(): bool
    {
        return $this === self::Postponement;
    }

    /**
     * الحصول على الحالات التي تحتاج تذكير
     */
    public static function needsReminder(): array
    {
        return [
            self::SubstantiveRuling,
            self::FormalRuling,
            self::RulingAffirmation,
        ];
    }

    /**
     * الحصول على الحالات النهائية
     */
    public static function finalStates(): array
    {
        return [
            self::CaseStrikingOff,
            self::Settlement,
            self::CasePreservation,
            self::SubstantiveRuling,
            self::FormalRuling,
            self::RulingAffirmation,
        ];
    }

    /**
     * تحديد نوع التذكير المطلوب
     */
    public function getReminderType(): ?string
    {
        return match ($this) {
            self::SubstantiveRuling, self::FormalRuling => 'objection_deadline',
            self::RulingAffirmation => 'execution_follow_up',
            default => null,
        };
    }

    /**
     * الحصول على وصف الحالة
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::RaisedForConsideration => 'تم رفع القضية للنظر من قبل المحكمة',
            self::None                   => 'لا يوجد تقرير إجمالي حتى الآن',
            self::CaseStrikingOff        => 'تم شطب الدعوى من جدول المحكمة',
            self::Settlement             => 'تم التوصل إلى تسوية ودية بين الأطراف',
            self::StayOfProceedings      => 'تم وقف إجراءات النظر في الدعوى',
            self::Postponement           => 'تم تأجيل الجلسة لموعد آخر',
            self::CasePreservation       => 'تم حفظ الدعوى',
            self::SubstantiveRuling      => 'صدر حكم في موضوع الدعوى',
            self::FormalRuling           => 'صدر حكم شكلي في الدعوى',
            self::RulingAffirmation      => 'تم تأييد الحكم من محكمة أعلى',
        };
    }

    /**
     * تحديد الخطوات التالية المطلوبة
     */
    public function getNextSteps(): array
    {
        return match ($this) {
            self::RaisedForConsideration => ['انتظار موعد الجلسة القادمة'],
            self::None                   => ['متابعة إجراءات الدعوى'],
            self::CaseStrikingOff        => ['إغلاق ملف الدعوى'],
            self::Settlement             => ['توثيق التسوية', 'إغلاق ملف الدعوى'],
            self::StayOfProceedings      => ['انتظار رفع أسباب الوقف'],
            self::Postponement           => ['تحديد موعد الجلسة الجديدة'],
            self::CasePreservation       => ['أرشفة ملف الدعوى'],
            self::SubstantiveRuling      => ['مراجعة إمكانية الاستئناف', 'متابعة مهلة الاعتراض'],
            self::FormalRuling           => ['مراجعة إمكانية الاستئناف', 'متابعة مهلة الاعتراض'],
            self::RulingAffirmation      => ['تنفيذ الحكم', 'متابعة إجراءات التنفيذ'],
        };
    }
}