<?php

namespace App\Models\LegalAffair\Session;


use Alkoumi\LaravelHijriDate\Hijri;
use App\Enums\LegalAffair\Session\SessionCompletion\SummaryReportStatus;
use App\Enums\LegalAffair\Session\SessionStatus;
use App\Models\AssignedSession;
use App\Models\general_setting\SettingsEntityRank;
use App\Models\general_setting\SettingsSessionType;
use App\Models\general_setting\SettingsTypeRulings;
use App\Models\Hr\Employees\Employees;
use App\Models\judicial_affairs\Project;
use App\Models\LegalAffair\Lawsuit\Lawsuit;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Session extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, Cachable;

    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */
    protected $fillable = [
        'session_name',
        'session_date',
        'session_time',
        'project_id',
        'lawsuit_id',
        'notes',
        'summary_report_status',
        'execution_minutes',
        'expected_execution_date',
        'reminder_sent_at',
        'objection_reminder_sent_at',
        'report_sending_method',
        'entity_ranks_id',
        'session_control_attached',
        'rule_attached',
        'session_status',
        'session_type',
        'rule_type',
        'last_objection_deadline',
        'objection_status',
        'execution_format',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'session_status'              => SessionStatus::class,
        'summary_report_status'       => SummaryReportStatus::class,

        'session_date'              => 'date',
        'session_time'              => 'datetime:H:i',
        'expected_execution_date'   => 'date',
        'last_objection_deadline'   => 'date',
        'reminder_sent_at'          => 'datetime',
        'objection_reminder_sent_at' => 'datetime',
    ];

    protected $dates = [
        'deleted_at',
        'reminder_sent_at',
        'objection_reminder_sent_at',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created'           => 'تم إنشاء الجلسة',
            'updated'           => 'تم تحديث الجلسة',
            'deleted'           => 'تم حذف الجلسة',
            'restored'          => 'تم استعادة الجلسة',
            'forceDeleted'      => 'تم حذف الجلسة بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('session')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} الجلسة";
            });
    }

    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */
    public function lawsuit()
    {
        return $this->belongsTo(Lawsuit::class, 'lawsuit_id');
    }

    public function sessionType()
    {
        return $this->belongsTo(SettingsSessionType::class, 'session_type');
    }

    public function ruleType()
    {
        return $this->belongsTo(SettingsTypeRulings::class, 'rule_type');
    }

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'assigned_sessions', 'session_id', 'assigned_to')
            ->using(AssignedSession::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function comments()
    {
        return $this->hasMany(SessionComment::class);
    }

    public function  entity_rank()
    {
        return $this->belongsTo(SettingsEntityRank::class, 'entity_ranks_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(Employees::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(Employees::class, 'updated_by');
    }




    /*
    |============================================================================
    |============================================================================
    |                               Scope
    |============================================================================
    |============================================================================
    */
    // ترتيب الجلسات حسب الاقرب
    public function scopeOrderByProximity($query)
    {
        return $query->orderByRaw("
        CASE
            WHEN CONCAT(session_date, ' ', IFNULL(session_time, '00:00:00')) >= NOW()
            THEN 0
            ELSE 1
        END ASC,
        session_date ASC,
        session_time ASC
    ");
    }


    /*
    |============================================================================
    |============================================================================
    |                               Access
    |============================================================================
    |============================================================================
    */
    public function getPrimaryContractCustomerIdAttribute()
    {
        return $this->lawsuit->project->primaryContract()->first()->customer_id;
    }


    public function getHijriSessionDateAttribute()
    {
        if ($this->session_date) {
            if ($this->isHijriDate($this->session_date)) {
                return $this->session_date;
            } else {
                return Hijri::ShortDate($this->session_date);
            }
        }
        return null;
    }


    // للحصول على تاريخ ووقت الجلسة
    public function getSessionDateTime()
    {
        if ($this->session_date && $this->session_time) {
            try {
                // استخدام Carbon::parse مباشرة
                $dateString = $this->session_date->format('Y-m-d');
                $timeString = Carbon::parse($this->session_time)->format('H:i:s');

                return Carbon::parse($dateString . ' ' . $timeString);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    // كم تبقي للجلسة
    public function getTimeRemainingAttribute()
    {
        if (!$this->session_date || !$this->session_time) {
            return '<span class="badge bg-label-secondary">غير متاح</span>';
        }

        $sessionDateTime = Carbon::parse(
            $this->session_date->format('Y-m-d') . ' ' .
                Carbon::parse($this->session_time)->format('H:i:s')
        );

        if ($sessionDateTime->isPast()) {
            return '<span class="badge bg-label-danger fw-bold">انتهت الجلسة</span>';
        }

        Carbon::setLocale('ar');

        $diff = $sessionDateTime->diffForHumans([
            'syntax' => CarbonInterface::DIFF_ABSOLUTE,
            'parts'  => 2,
            'short'  => true,
            'join'   => ' و ',
        ]);

        return '<span class="badge bg-label-success fw-bold">تبقى ' . $diff . '</span>';
    }


    public function isSessionExpired(): bool
    {
        $sessionDateTime = $this->getSessionDateTime();
        return $sessionDateTime && $sessionDateTime->lte(Carbon::now());
    }






























    // للتاريخ الهجري
    //لتحويل التاريخ لميلادي اذا كان هجري او تركه اذا كان ميلادي
    public function setSessionDateAttribute($value)
    {
        if ($this->isHijriDate($value)) {
            // تقسيم التاريخ الهجري إلى يوم، شهر، وسنة
            list($year, $month, $day) = explode('-', $value);
            // تحويل التاريخ الهجري إلى ميلادي
            $gregorianDate = Hijri::DateToGregorianFromDMY($day, $month, $year);
            // تخزين التاريخ الميلادي في قاعدة البيانات
            $this->attributes['session_date'] = $gregorianDate;
        } else {

            // تخزين التاريخ الميلادي مباشرة
            $this->attributes['session_date'] = Carbon::parse($value)->format('Y-m-d');
        }
    }

    public function setLastObjectionDeadlineAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['last_objection_deadline'] = null;
            return;
        }

        if ($this->isHijriDate($value)) {
            // تقسيم التاريخ الهجري إلى يوم، شهر، وسنة
            list($year, $month, $day) = explode('-', $value);
            // تحويل التاريخ الهجري إلى ميلادي
            $gregorianDate = Hijri::DateToGregorianFromDMY($day, $month, $year);
            // تخزين التاريخ الميلادي في قاعدة البيانات
            $this->attributes['last_objection_deadline'] = $gregorianDate;
        } else {

            // تخزين التاريخ الميلادي مباشرة
            $this->attributes['last_objection_deadline'] = Carbon::parse($value)->format('Y-m-d');
        }
    }

    public function getHijriLastObjectionAttribute()
    {
        // التحقق من أن تاريخ البداية موجود
        if ($this->last_objection_deadline) {
            if ($this->isHijriDate($this->last_objection_deadline)) {
                // التاريخ هجري بالفعل، لا حاجة للتحويل
                return $this->last_objection_deadline;
            } else {
                // التاريخ ميلادي، قم بتحويله إلى هجري
                // افترض أن التاريخ الميلادي بتنسيق 'Y-m-d'
                return Hijri::ShortDate($this->last_objection_deadline);
            }
        }
        return null; // في حال كان التاريخ فارغاً
    }

    // ExpectedExecutionDate
    public function setExpectedExecutionDateAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['expected_execution_date'] = null;
            return;
        }

        if ($this->isHijriDate($value)) {
            // تقسيم التاريخ الهجري إلى يوم، شهر، وسنة
            list($year, $month, $day) = explode('-', $value);
            // تحويل التاريخ الهجري إلى ميلادي
            $gregorianDate = Hijri::DateToGregorianFromDMY($day, $month, $year);
            // تخزين التاريخ الميلادي في قاعدة البيانات
            $this->attributes['expected_execution_date'] = $gregorianDate;
        } else {

            // تخزين التاريخ الميلادي مباشرة
            $this->attributes['expected_execution_date'] = Carbon::parse($value)->format('Y-m-d');
        }
    }

    public function getHijriExpectedExecutionAttribute()
    {
        // التحقق من أن تاريخ البداية موجود
        if ($this->expected_execution_date) {
            if ($this->isHijriDate($this->expected_execution_date)) {
                // التاريخ هجري بالفعل، لا حاجة للتحويل
                return $this->expected_execution_date;
            } else {
                // التاريخ ميلادي، قم بتحويله إلى هجري
                // افترض أن التاريخ الميلادي بتنسيق 'Y-m-d'
                return Hijri::ShortDate($this->expected_execution_date);
            }
        }
        return null; // في حال كان التاريخ فارغاً
    }






    /*
    |============================================================================
    |============================================================================
    |                          Private methods
    |============================================================================
    |============================================================================
    */
    private function isHijriDate($date)
    {
        return preg_match('/^(13|14|15)\d{2}-\d{2}-\d{2}$/', $date);
    }

    public static function toGregorian(string $value): Carbon
    {
        // إذا كانت السنة في النطاق 1300-1600 نفترض أنه هجري
        if (preg_match('/^(13|14|15)\d{2}-\d{2}-\d{2}$/', $value)) {
            [$y, $m, $d] = Hijri::DateToGregorianFromDMY(
                substr($value, -2),        // اليوم
                substr($value, 5, 2),      // الشهر
                substr($value, 0, 4)       // السنة الهجرية
            );
            $value = sprintf('%04d-%02d-%02d', $y, $m, $d);
        }

        return Carbon::createFromFormat('Y-m-d', $value);
    }
}
