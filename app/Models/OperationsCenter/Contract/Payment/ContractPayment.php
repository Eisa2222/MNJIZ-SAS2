<?php

namespace App\Models\OperationsCenter\Contract\Payment;

use App\Enums\OperationsCenter\Contract\Payment\CalculationType;
use App\Enums\OperationsCenter\Contract\Payment\PaymentBatchType;
use App\Enums\OperationsCenter\Contract\Payment\PaymentMethod;
use App\Enums\OperationsCenter\Contract\Payment\PaymentStatus;
use App\Models\Hr\Employees\Employees;
use App\Models\OperationsCenter\Contract\Contract;
use App\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractPayment extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;
    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */
    protected $fillable = [
        'contract_id',
        'calculation_type',
        'payment_batch_type',
        'percentage',
        'fixed_amount',
        'currency',
        'due_date',
        'payment_date',
        'payment_method',
        'status',
        'paid_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'due_date'              => 'date',
        'payment_date'          => 'date',
        'status'                => PaymentStatus::class,
        'calculation_type'      => CalculationType::class,
        'payment_batch_type'    => PaymentBatchType::class,
        'payment_method'        => PaymentMethod::class,

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
        return $this->belongsTo(Contract::class);
    }

    public function paidBy()
    {
        return $this->belongsTo(Employees::class, 'paid_by');
    }

    public function creator()
    {
        return $this->belongsTo(Employees::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(Employees::class, 'updated_by');
    }



    /*
    |============================================================================
    |============================================================================
    |                               Accessors
    |============================================================================
    |============================================================================
    */
    /** المدة المتبقية بالأيام إذا كان موعد السداد فى المستقبل */
    public function getDaysRemainingAttribute(): ?int
    {
        return $this->due_date && $this->due_date->isFuture()
            ? now()->diffInDays($this->due_date)
            : null;
    }

    /** المدة المتأخرة بالأيام إذا فات موعد السداد */
    public function getDaysOverdueAttribute(): ?int
    {
        return $this->due_date && $this->due_date->isPast()
            ? $this->due_date->diffInDays(now())
            : null;
    }
}
