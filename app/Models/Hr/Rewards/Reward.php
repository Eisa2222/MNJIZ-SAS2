<?php

namespace App\Models\Hr\Rewards;

use App\Enums\Hr\Reward\RewardStatus;
use App\Enums\Hr\Reward\RewardType;
use App\Models\Hr\Employees\Employees;
use App\Tenancy\Concerns\BelongsToTenant;
use App\Traits\ApprovalWorkflow\HasApprovalWorkflow;
use Carbon\Carbon;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Reward extends Model
{
    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */

    use HasFactory, SoftDeletes, LogsActivity, HasApprovalWorkflow, BelongsToTenant;
    use Cachable;

    protected $fillable = [
        'reward_number',
        'employee_id',
        'reward_type',
        'amount',
        'reward_date',
        'status',
        'created_by',
        'updated_by',
        'notes',

        'applied_to_salary_date',
        'wps_payrolls_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created'       => 'تم إنشاء مكافأة جديدة',
            'updated'       => 'تم تحديث مكافأة موجودة مسبقا',
            'deleted'       => 'تم حذف مكافأة ',
            'restored'      => 'تم استعادة مكافأة ',
            'forceDeleted'  => 'تم حذف مكافأة بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('settings')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} مكافأة";
            });
    }


    protected $casts = [
        'status'          => RewardStatus::class,
        'reward_type'     => RewardType::class,
        'reward_date'     => 'date:Y-m-d',

        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];


    /*
    |--------------------------------------------------------------------------
    | Flow Type
    |--------------------------------------------------------------------------
    */
    protected string $flowType = 'reward';

    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */
    public function employee()
    {
        return $this->belongsTo(Employees::class);
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
    |                            scopes
    |============================================================================
    |============================================================================
    */
    public function scopeApprovedInMonth($query, int $employeeId, Carbon $month)
    {
        return $query->where('employee_id', $employeeId)
            ->where('status', RewardStatus::Approved)
            ->whereYear('reward_date', $month->year)
            ->whereMonth('reward_date', $month->month);
    }
}
