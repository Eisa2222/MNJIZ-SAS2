<?php

namespace App\Models\ElectronicServices\Custody\Request;

use App\Enums\ElectronicServices\Custody\Requests\CustodyRequestStatus;
use App\Enums\ElectronicServices\Custody\Requests\CustodyRequestType;
use App\Enums\ElectronicServices\Custody\Requests\CustodyReturnStatus;
use App\Models\Hr\Custody\Item\CustodyItem;
use App\Models\Hr\Employees\Employees;
use App\Traits\ApprovalWorkflow\HasApprovalWorkflow;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CustodyRequest extends Model
{
    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */

    use HasFactory, SoftDeletes, LogsActivity, HasApprovalWorkflow;
    // use Cachable;

    protected $fillable = [
        'request_type',
        'parent_request_id',
        'employee_id',
        'custody_item_id',
        'start_date',
        'end_date',
        'status',
        'notes',
        'return_status',

        'created_by',
        'updated_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created'       => 'تم إنشاء عهدة جديدة',
            'updated'       => 'تم تحديث عهدة موجودة مسبقا',
            'deleted'       => 'تم حذف عهدة ',
            'restored'      => 'تم استعادة عهدة ',
            'forceDeleted'  => 'تم حذف عهدة بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('settings')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} عهدة";
            });
    }


    protected $casts = [
        'request_type'      => CustodyRequestType::class,
        'status'            => CustodyRequestStatus::class,
        'return_status'     => CustodyReturnStatus::class,

        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'deleted_at'    => 'datetime',
    ];


    /*
    |--------------------------------------------------------------------------
    | Flow Type
    |--------------------------------------------------------------------------
    */
    protected string $flowType = 'custody';

    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */
    public function parentRequest()
    {
        return $this->belongsTo(self::class, 'parent_request_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id');
    }

    public function item()
    {
        return $this->belongsTo(CustodyItem::class, 'custody_item_id');
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
    public function scopeAssign($query)
    {
        return $query->where('request_type', CustodyRequestType::Assign);
    }

    public function scopeReturn($query)
    {
        return $query->where('request_type', CustodyRequestType::Return);
    }

    public function scopePending($query)
    {
        return $query->where('status', CustodyRequestStatus::Pending);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', CustodyRequestStatus::Approved);
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }
}
