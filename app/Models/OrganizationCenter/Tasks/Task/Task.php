<?php

namespace App\Models\OrganizationCenter\Tasks\Task;

use App\Enums\OrganizationCenter\Tasks\Task\TaskField;
use App\Enums\OrganizationCenter\Tasks\Task\TaskPriority;
use App\Enums\OrganizationCenter\Tasks\Task\TaskStatus;
use App\Http\Requests\Marketing\ContentManagement\ContentManagementRequest;
use App\Models\ElectronicServices\Custody\Request\CustodyRequest;
use App\Models\ElectronicServices\LeaveRequests\LeaveRequest;
use App\Models\Hr\Advances\Advance;
use App\Models\Hr\Payrolls\WPS\WpsPayroll;
use App\Models\Hr\Rewards\Reward;
use App\Models\judicial_affairs\Project;
use App\Models\LegalAffair\Lawsuit\Lawsuit;
use App\Models\LegalAffair\PowerOfAttorney\PowerOfAttorney;
use App\Models\LegalAffair\Session\Session;
use App\Models\Marketing\ContentManagement\ContentManagement;
use App\Models\OperationsCenter\Contract\Contract;
use App\Models\OperationsCenter\Offer\Offers;
use App\Models\OrganizationCenter\Tasks\TaskStep\TaskStep;
use App\Models\Self_services\ClearanceCertificate;
use App\Models\User;
use App\Tenancy\Concerns\BelongsToTenant;
use Carbon\Carbon;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Task extends Model
{
    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */
    use HasFactory, SoftDeletes, LogsActivity, BelongsToTenant;
    use Cachable;


    protected $fillable = [
        'task_name',
        'priority',
        'description',
        'task_field',

        // field
        'offer_id',
        'contract_id',
        'project_id',
        'lawsuit_id',
        'session_id',
        'power_of_attorney_id',

        'clearance_certificate_id',
        'advance_id',
        'reward_id',
        'deduction_id',
        'content_management_id',
        'custody_id',
        'leave_id',
        'wps_id',

        'marketing_id',
        'detailed_marketing_channel_id',
        'customer_id',
        'social_media_id',

        'status',
        'due_date',
        'due_time',
        'task_start_date',
        'task_end_date',
        'attachment',
        'type_task',
        'created_by',
        'completed_by',
        'updated_by'
    ];


    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created'       => 'تم إنشاء مهمة جديدة',
            'updated'       => 'تم تحديث مهمة موجودة مسبقا',
            'deleted'       => 'تم حذف مهمة ',
            'restored'      => 'تم استعادة مهمة ',
            'forceDeleted'  => 'تم حذف مهمة بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('settings')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} مهمة";
            });
    }


    protected $casts = [
        'status'            => TaskStatus::class,
        'task_field'        => TaskField::class,
        'priority'          => TaskPriority::class,

        'task_start_date'   => 'datetime',
        'task_end_date'     => 'datetime',

        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];


    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */
    public function steps()
    {
        return $this->hasMany(TaskStep::class);
    }

    //العلاقة مع المستخدم الذي تم إسناد المهمة إليه
    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->withPivot('graph_list_id', 'graph_task_id', 'graph_event_id')
            ->withTimestamps();
    }

    public function offer()
    {
        return $this->belongsTo(Offers::class, 'offer_id');
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function lawsuit()
    {
        return $this->belongsTo(Lawsuit::class, 'lawsuit_id');
    }

    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id');
    }

    public function powerOfAttorney()
    {
        return $this->belongsTo(PowerOfAttorney::class, 'power_of_attorney_id');
    }

    public function clearanceCertificate()
    {
        return $this->belongsTo(ClearanceCertificate::class, 'clearance_certificate_id');
    }

    public function advance()
    {
        return $this->belongsTo(Advance::class, 'advance_id');
    }

    public function reward()
    {
        return $this->belongsTo(Reward::class, 'reward_id');
    }

    public function deduction()
    {
        return $this->belongsTo(Reward::class, 'deduction_id');
    }

    public function contentManagement()
    {
        return $this->belongsTo(ContentManagement::class, 'content_management_id');
    }

    public function custody()
    {
        return $this->belongsTo(CustodyRequest::class, 'custody_id');
    }

    public function leave()
    {
        return $this->belongsTo(LeaveRequest::class, 'leave_id');
    }

    public function wps()
    {
        return $this->belongsTo(WpsPayroll::class, 'wps_id');
    }

    public function tasksLogs()
    {
        return $this->hasMany(TaskEvent::class);
    }

    public function routings()
    {
        return $this->hasMany(TaskRouting::class);
    }

    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }


    /*
    |============================================================================
    |============================================================================
    |                               Scope
    |============================================================================
    |============================================================================
    */
    public function scopeOwnedBy($query)
    {
        return $query->where('created_by', auth()->id());
    }

    public function getDurationAttribute()
    {
        if (!$this->task_start_date || !$this->task_end_date) {
            return null;
        }

        $start              = Carbon::parse($this->task_start_date);
        $end                = Carbon::parse($this->task_end_date);
        $durationMinutes    = $end->diffInMinutes($start);

        $hours              = floor($durationMinutes / 60);
        $minutes            = $durationMinutes % 60;

        if ($hours > 0) {
            return $hours . ' ساعة ' . ($minutes > 0 ? $minutes . ' دقيقة' : '');
        }
        return $minutes . ' دقيقة';
    }


    /*
    |============================================================================
    |============================================================================
    |                          Accessors
    |============================================================================
    |============================================================================
    */
    public function canToggleCompletion(?int $userId = null): bool
    {
        $userId = $userId ?? auth()->id();

        if (!$userId) {
            return false;
        }
        return $this->assignedUsers->contains('id', $userId);
    }

    public function canShowActions(?int $userId = null): bool
    {
        $userId = $userId ?? auth()->id();

        if (!$userId) {
            return false;
        }

        // إذا كانت المهمة مُرجعة، تحقق من إمكانية الرد
        if ($this->status->value === 'returned') {
            return $this->canRespondToReturn($userId);
        }

        // إذا كانت هناك خطوات مرفوضة، لا يمكن أي إجراء
        if ($this->steps->where('status', 'rejected')->count() > 0) {
            return false;
        }

        // يجب أن يكون مكلف بالمهمة
        return $this->assignedUsers->contains('id', $userId);
    }


    public function canRespondToReturn(?int $userId = null): bool
    {
        $userId = $userId ?? auth()->id();

        if (!$userId || $this->status->value !== 'returned') {
            return false;
        }

        // آخر حركة إرجاع
        $lastRouting = $this->routings->sortByDesc('created_at')->first();

        return $lastRouting &&
            $lastRouting->action->value === 'return' &&
            $this->created_by  === $userId;
    }

    public function canComplete(?int $userId = null): bool
    {
        $userId = $userId ?? auth()->id();

        return $this->canToggleCompletion($userId) &&
            $this->status->value !== 'completed' &&
            $this->status->value !== 'returned' &&
            $this->steps->where('status', 'rejected')->count() === 0;
    }

    public function canIncomplete(?int $userId = null): bool
    {
        $userId = $userId ?? auth()->id();

        return $this->canToggleCompletion($userId) &&
            $this->status->value === 'completed' &&
            $this->steps->where('status', 'rejected')->count() === 0;
    }

    public function canReturn(?int $userId = null): bool
    {
        $userId = $userId ?? auth()->id();

        return $this->canToggleCompletion($userId) &&
            $this->status->value !== 'completed' &&
            $this->status->value !== 'returned' &&
            $this->steps->where('status', 'rejected')->count() === 0;
    }

    // التحقق من كون المهمة مُرجعة ويمكن الرد عليها
    public function isReturnedAndCanRespond(?int $userId = null): bool
    {
        return $this->status->value === 'returned' && $this->canRespondToReturn($userId);
    }

    public function getStatusMessage(): string
    {
        if ($this->status->value === 'returned') {
            if ($this->canRespondToReturn()) {
                return '';
            } else {
                return 'تم إرجاع المهمة وفي انتظار الرد';
            }
        }

        if ($this->steps->where('status', 'rejected')->count() > 0) {
            return 'تم رفض الاعتماد';
        }

        return '';
    }







    /*
    |--------------------------------------------------------------------------
    | check if all steps complate or approved
    |--------------------------------------------------------------------------
    */
    public function areAllStepsCompletedOrApproved()
    {
        // إذا لم تكن هناك خطوات، يمكن اعتبار المهمة بدون خطوات "مكتملة" بشكل افتراضي.
        if ($this->steps->isEmpty()) {
            return true;
        }

        // تحقق من أن كل خطوة حالتها 'completed' أو 'approved'
        return $this->steps->every(function ($step) {
            return in_array($step->status, ['completed', 'approved']);
        });
    }

















    /*
    |--------------------------------------------------------------------------
    | check is assig
    |--------------------------------------------------------------------------
    | Description of this section.
    */
    public function isAssignedUser()
    {
        return $this->assignedUsers->contains('id', auth()->id());
    }
}
