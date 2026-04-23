<?php

namespace App\Models\OperationsCenter\ExceptionalContract;

use App\Models\OperationsCenter\Customer\Customers;
use App\Models\Hr\Employees\Employees;
use App\Models\judicial_affairs\Project;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExceptionalContract extends Model
{
    use HasFactory, SoftDeletes;
    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */
    protected $fillable = [
        'contract_name',
        'customer_id',
        'employee_id',
        'scope_of_work',
        'reasons',
        'equivalent',
        'project_name',
        'created_by_id',
        'updated_by_id',
        'status',

    ];

    // status
    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected static array $statusOptions = [
        self::STATUS_PENDING  => [
            'bg'   => 'secondary',
            'name' => self::STATUS_PENDING,
            'text' => 'قيد الانتظار',
        ],
        self::STATUS_APPROVED => [
            'bg'   => 'success',
            'name' => self::STATUS_APPROVED,
            'text' => 'معتمد',
        ],
        self::STATUS_REJECTED => [
            'bg'   => 'danger',
            'name' => self::STATUS_REJECTED,
            'text' => 'مرفوض',
        ],
    ];


    /*
    |============================================================================
    |============================================================================
    |                               Boot Methods
    |============================================================================
    |============================================================================
    */
    protected static function booted()
    {
        static::created(function (self $contract) {
            $contract->setupApprovals();
        });
    }

    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id');
    }

    public function creator()
    {
        return $this->belongsTo(Employees::class, 'created_by_id');
    }

    public function approvals()
    {
        return $this->hasMany(ExceptionalContractApproval::class, 'contract_id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }


    /*
    |============================================================================
    |============================================================================
    |                               Accessors
    |============================================================================
    |============================================================================
    */
    // get status badge by status
    public function getStatusBadgeAttribute(): array
    {
        return self::$statusOptions[$this->status]
            ?? self::$statusOptions[self::STATUS_PENDING];
    }

    // get status name by status
    public function getStatusNameAttribute(): string
    {
        return $this->statusBadge['text'];
    }


    /*
    |============================================================================
    |============================================================================
    |                          Custom Methods
    |============================================================================
    |============================================================================
    */
    // get all status
    public static function getStatusOptions(): array
    {
        return self::$statusOptions;
    }


    public function setupApprovals(): void
    {
        $this->approvals()->delete();

        $one = Employees::where('name', 'سعيد محمد سعيد القرني')->value('id');
        $two = Employees::where('name', 'حسين عبدالله علي الزهراني')->value('id');
        $creator = $this->created_by_id;

        $batch = [];

        if ($creator === $one) {
            $batch[] = ['approver_id' => $one, 'status' => 'approved'];
            $batch[] = ['approver_id' => $two, 'status' => 'pending'];
        } elseif ($creator === $two) {
            $batch[] = ['approver_id' => $two, 'status' => 'approved'];
            $batch[] = ['approver_id' => $one, 'status' => 'pending'];
        } else {
            $batch[] = ['approver_id' => $one, 'status' => 'pending'];
            $batch[] = ['approver_id' => $two, 'status' => 'pending'];
        }

        foreach ($batch as $data) {
            $this->approvals()->create($data);
        }
    }

    public function isRejected(): bool
    {
        return $this->approvals()->where('status', 'rejected')->exists();
    }

    public function pendingApprovalFor($user)
    {
        if ($this->isRejected()) return null;

        $user = auth()->user()->employee;

        return $this->approvals()->where('approver_id', $user->id)->where('status', 'pending')->first();
    }

    public function resetApprovals(): void
    {
        $this->setupApprovals();
    }

    public function changeStatus($status)
    {
        if (! array_key_exists($status, self::$statusOptions)) {
            throw new \InvalidArgumentException("Invalid status: {$status}");
        }

        $this->update(['status' => $status]);
    }
}
