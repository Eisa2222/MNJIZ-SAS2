<?php

namespace App\Models\ApprovalSystem;

use App\Models\Hr\Employees\Employees;
use App\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalRequestLevel extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'approval_request_id',
        'level',
        'employee_id',
    ];

    protected $casts = [
        'level' => 'integer',
        'employee_id' => 'integer',
        'approval_request_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

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
     * العلاقة مع الموظف المعيّن
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
     * الحصول على اسم الموظف
     */
    public function getEmployeeName(): string
    {
        return $this->employee ? $this->employee->name : 'غير محدد';
    }

    /**
     * الحصول على اسم الموظف مع الكنية
     */
    public function getEmployeeDisplayName(): string
    {
        return $this->employee ? $this->employee->name : 'غير محدد';
    }

    /**
     * الحصول على تسمية المستوى
     */
    public function getLevelLabel(): string
    {
        return "المستوى {$this->level}";
    }

    /**
     * التحقق من وجود توقيع للموظف
     */
    public function hasEmployeeSignature(): bool
    {
        return $this->employee && !empty($this->employee->signature);
    }

    /**
     * الحصول على معلومات المستوى
     */
    public function getLevelInfo(): array
    {
        return [
            'level' => $this->level,
            'employee_id' => $this->employee_id,
            'employee_name' => $this->getEmployeeDisplayName(),
            'level_label' => $this->getLevelLabel(),
            'has_signature' => $this->hasEmployeeSignature(),
        ];
    }
}
