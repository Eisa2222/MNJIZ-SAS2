<?php

namespace App\Models\ApprovalSystem;

use App\Models\Hr\Employees\Employees;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalLog extends Model
{
    protected $fillable = [
        'approval_request_id',
        'level',
        'employee_id',
        'action',
        'reason',
        'additional_data',
    ];

    protected $casts = [
        'employee_id' => 'integer',
        'level' => 'integer',
        'additional_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const ACTION_APPROVED = 'approved';
    const ACTION_REJECTED = 'rejected';
    const ACTION_REVOKED = 'revoked';

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * العلاقة مع طلب الاعتماد
     */
    public function approvalRequest(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequest::class);
    }

    /**
     * العلاقة مع الموظف الذي قام بالإجراء
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employees::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * الحصول على تسمية الإجراء
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_APPROVED => 'تم الاعتماد',
            self::ACTION_REJECTED => 'تم الرفض',
            self::ACTION_REVOKED => 'تم إلغاء الاعتماد',
            default => $this->action,
        };
    }

    /**
     * الحصول على لون الإجراء للعرض
     */
    public function getActionColorAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_APPROVED => 'success',
            self::ACTION_REJECTED => 'danger',
            self::ACTION_REVOKED => 'warning',
            default => 'secondary',
        };
    }

    /**
     * الحصول على أيقونة الإجراء
     */
    public function getActionIconAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_APPROVED => 'ti ti-check',
            self::ACTION_REJECTED => 'ti ti-x',
            self::ACTION_REVOKED => 'ti ti-rotate-clockwise',
            default => 'ti ti-info-circle',
        };
    }

    /**
     * التحقق من وجود توقيع
     */
    public function hasSignature(): bool
    {
        return !empty($this->signature_path);
    }

    /**
     * الحصول على رابط التوقيع
     */
    public function getSignatureUrlAttribute(): ?string
    {
        return $this->signature_path ? asset('storage/' . $this->signature_path) : null;
    }

    /**
     * الحصول على تفاصيل الإجراء
     */
    public function getActionDetailsAttribute(): array
    {
        return [
            'action' => $this->action,
            'label' => $this->action_label,
            'color' => $this->action_color,
            'icon' => $this->action_icon,
            'employee_name' => $this->employee?->name ?? 'غير معروف',
            'level' => $this->level,
            'date' => $this->created_at?->format('d/m/Y H:i'),
            'has_reason' => !empty($this->reason),
            'has_signature' => $this->hasSignature(),
        ];
    }
}
