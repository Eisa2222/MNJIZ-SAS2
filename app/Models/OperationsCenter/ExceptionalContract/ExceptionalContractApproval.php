<?php

namespace App\Models\OperationsCenter\ExceptionalContract;

use App\Models\Hr\Employees\Employees;
use App\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExceptionalContractApproval extends Model
{
    use HasFactory, BelongsToTenant;
    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */
    protected $fillable = [
        'contract_id',
        'approver_id',
        'status',
        'reason',
        'approved_at',
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
    |                               Relations
    |============================================================================
    |============================================================================
    */
    public function contract()
    {
        return $this->belongsTo(ExceptionalContract::class, 'contract_id');
    }

    public function approver()
    {
        return $this->belongsTo(Employees::class, 'approver_id');
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
}
