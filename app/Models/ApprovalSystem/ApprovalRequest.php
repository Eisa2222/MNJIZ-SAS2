<?php

namespace App\Models\ApprovalSystem;

use App\Models\Hr\Employees\Employees;
use App\Models\SystemAdministration\CredentialsManagement\ApprovalFlow;
use App\Models\User;
use App\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;

class ApprovalRequest extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'approval_flow_id',
        'approvable_type',
        'approvable_id',
        'current_level',
        'status',
        'requested_by_user_id',
        'completed_at',
    ];

    protected $casts = [
        'current_level' => 'integer',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ثوابت الحالات
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';


    /*
    |--------------------------------------------------------------------------
    | العلاقة مع تدفق الاعتماد (للمرجع فقط)
    |--------------------------------------------------------------------------
    */
    public function flow(): BelongsTo
    {
        return $this->belongsTo(ApprovalFlow::class, 'approval_flow_id');
    }

    /*
    |--------------------------------------------------------------------------
    | العلاقة البوليمورفيك مع النموذج المطلوب اعتماده
    |--------------------------------------------------------------------------
    */
    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    /*
    |--------------------------------------------------------------------------
    | العلاقة مع المستخدم الذي طلب الاعتماد
    |--------------------------------------------------------------------------
    */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | العلاقة مع سجل العمليات
    |--------------------------------------------------------------------------
    */
    public function logs(): HasMany
    {
        return $this->hasMany(ApprovalLog::class)->orderBy('created_at', 'desc');
    }

    /*
    |--------------------------------------------------------------------------
    | العلاقة مع مستويات الاعتماد المخزنة (Snapshot)
    |--------------------------------------------------------------------------
    */
    public function requestLevels(): HasMany
    {
        return $this->hasMany(ApprovalRequestLevel::class)->orderBy('level');
    }


    /*
    |--------------------------------------------------------------------------
    | التحقق من أن الطلب قيد الانتظار
    |--------------------------------------------------------------------------
    */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /*
    |--------------------------------------------------------------------------
    | التحقق من أن الطلب معتمد
    |--------------------------------------------------------------------------
    */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /*
    |--------------------------------------------------------------------------
    | التحقق من أن الطلب مرفوض
    |--------------------------------------------------------------------------
    */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /*
    |--------------------------------------------------------------------------
    |  الحصول على المعتمد الحالي للمستوى
    |--------------------------------------------------------------------------
    */
    public function getCurrentLevelApprover(): ?Employees
    {
        $level = $this->requestLevels()->where('level', $this->current_level)->first();
        return $level ? $level->employee : null;
    }

    /*
    |--------------------------------------------------------------------------
    | الحصول على المعتمد للمستوى التالي
    |--------------------------------------------------------------------------
    */
    public function getNextLevelApprover(): ?Employees
    {
        $nextLevel = $this->requestLevels()->where('level', $this->current_level + 1)->first();
        return $nextLevel ? $nextLevel->employee : null;
    }

    /*
    |--------------------------------------------------------------------------
    | حساب نسبة الإنجاز
    |--------------------------------------------------------------------------
    */
    public function getCompletionPercentage(): float
    {
        $totalLevels = $this->requestLevels()->count();
        if ($totalLevels === 0) return 0;

        if ($this->isApproved()) {
            return 100;
        }

        if ($this->isRejected()) {
            return 0;
        }

        $completedLevels = $this->current_level - 1;
        return round(($completedLevels / $totalLevels) * 100, 2);
    }


    /*
    |--------------------------------------------------------------------------
    | Get Approval Stages
    |--------------------------------------------------------------------------
    */
    public function getApprovalStages(): array
    {
        $currentEmployeeId = auth()->user()?->employee?->id;
        $stages = [];
        $levels = $this->requestLevels()->orderBy('level')->get();

        $actualHighestApprovedLevel = null;

        foreach ($levels as $level) {
            $lastLog = $this->logs()
                ->where('level', $level->level)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastLog && $lastLog->action === 'approved') {
                $actualHighestApprovedLevel = $level->level;
            }
        }

        foreach ($levels as $level) {
            $lastLog = $this->logs()
                ->where('level', $level->level)
                ->orderBy('created_at', 'desc')
                ->first();

            // تحديد الحالة الفعلية
            $status = $lastLog ? $lastLog->action : 'pending';
            if ($status === 'revoked') {
                $status = 'pending';
            }

            $isCurrent = ($this->current_level == $level->level && $this->isPending());

            $canAction = $isCurrent &&
                $currentEmployeeId == $level->employee_id &&
                $this->isPending() &&
                $status === 'pending';

            $canRevoke = false;
            if (
                $lastLog &&
                $lastLog->action === 'approved' &&
                (int)$lastLog->employee_id === (int)$currentEmployeeId &&
                $this->status !== 'rejected' &&
                $level->level === $actualHighestApprovedLevel
            ) { // ← المفتاح هنا!

                $canRevoke = true;
            }

            $stages[] = [
                'level' => $level->level,
                'employee_name' => $level->employee->name ?? 'غير محدد',
                'employee_id' => $level->employee_id,
                'status' => $status,
                'is_current' => $isCurrent,
                'can_action' => $canAction,
                'can_revoke' => $canRevoke,
                'action_date' => $lastLog?->created_at?->format('d/m/Y H:i'),
                'reason' => $lastLog?->reason,
                'signature_path' => $lastLog?->signature_path,
                'config' => self::getStageStatusConfig($status),
            ];
        }

        return $stages;
    }

    /*
    |--------------------------------------------------------------------------
    | الحصول على تسمية الحالة
    |--------------------------------------------------------------------------
    */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'قيد الانتظار',
            self::STATUS_APPROVED => 'معتمد',
            self::STATUS_REJECTED => 'مرفوض',
            default => $this->status,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | الحصول على لون الحالة
    |--------------------------------------------------------------------------
    */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            default => 'secondary',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | الحصول على أيقونة الحالة
    |--------------------------------------------------------------------------
    */
    public function getStatusIconAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'ti-clock',
            self::STATUS_APPROVED => 'ti-check',
            self::STATUS_REJECTED => 'ti-x',
            default => 'ti-help',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | الحصول على معلومات المستوى الحالي
    |--------------------------------------------------------------------------
    */
    public function getCurrentLevelInfo(): array
    {
        $currentLevel = $this->requestLevels()
            ->where('level', $this->current_level)
            ->first();

        if (!$currentLevel) {
            return [];
        }

        return [
            'level' => $currentLevel->level,
            'employee_name' => $currentLevel->getEmployeeDisplayName(),
            'employee_id' => $currentLevel->employee_id,
            'is_assigned' => !is_null($currentLevel->employee_id),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | التحقق من إمكانية الانتقال للمستوى التالي
    |--------------------------------------------------------------------------
    */
    public function canProceedToNextLevel(): bool
    {
        return $this->isPending() &&
            $this->requestLevels()
            ->where('level', $this->current_level + 1)
            ->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | الحصول على إجمالي المستويات المفعلة
    |--------------------------------------------------------------------------
    */
    public function getTotalActiveLevels(): int
    {
        return $this->requestLevels()->count();
    }

    /*
    |--------------------------------------------------------------------------
    | الحصول على المستويات المكتملة
    |--------------------------------------------------------------------------
    */
    public function getCompletedLevels(): int
    {
        if ($this->isApproved()) {
            return $this->getTotalActiveLevels();
        }

        if ($this->isRejected()) {
            return 0;
        }

        return $this->current_level - 1;
    }

    /*
    |--------------------------------------------------------------------------
    | الحصول على معلومات التقدم
    |--------------------------------------------------------------------------
    */
    public function getProgressInfo(): array
    {
        $totalLevels = $this->getTotalActiveLevels();
        $completedLevels = $this->getCompletedLevels();

        return [
            'total_levels' => $totalLevels,
            'completed_levels' => $completedLevels,
            'current_level' => $this->current_level,
            'completion_percentage' => $this->getCompletionPercentage(),
            'is_completed' => $this->isApproved(),
            'is_rejected' => $this->isRejected(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | التحقق من وجود مستوى معين في الـ Snapshot
    |--------------------------------------------------------------------------
    */
    public function hasLevel(int $level): bool
    {
        return $this->requestLevels()->where('level', $level)->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | الحصول على مستوى معين من الـ Snapshot
    |--------------------------------------------------------------------------
    */
    public function getLevel(int $level): ?ApprovalRequestLevel
    {
        return $this->requestLevels()->where('level', $level)->first();
    }

    /*
    |--------------------------------------------------------------------------
    | الحصول على إعدادات عرض حالة المرحلة
    |--------------------------------------------------------------------------
    */
    public static function getStageStatusConfig(string $status): array
    {
        return match ($status) {
            'approved' => [
                'lineColor' => 'success',
                'bgClass' => 'bg-success',
                'textClass' => 'text-white',
                'icon' => 'ti-check',
            ],
            'rejected' => [
                'lineColor' => 'danger',
                'bgClass' => 'bg-danger',
                'textClass' => 'text-white',
                'icon' => 'ti-x',
            ],
            'pending' => [
                'lineColor' => 'warning',
                'bgClass' => 'bg-warning',
                'textClass' => 'text-white',
                'icon' => 'ti-clock',
            ],
            default => [
                'lineColor' => 'secondary',
                'bgClass' => 'bg-secondary',
                'textClass' => 'text-white',
                'icon' => 'ti-dots',
            ],
        };
    }
}
