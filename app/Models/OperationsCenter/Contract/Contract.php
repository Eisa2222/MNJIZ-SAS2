<?php

namespace App\Models\OperationsCenter\Contract;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Enums\Financial\ContractPayment\ContractPaymentsState;
use App\Enums\OperationsCenter\Contract\ContractStatus;
use App\Enums\OperationsCenter\Contract\Payment\PaymentStatus;
use App\Models\OperationsCenter\Customer\Customers;
use App\Models\general_setting\SettingsContractStatus;
use App\Models\Hr\Employees\Employees;
use App\Models\judicial_affairs\Project;
use App\Models\OperationsCenter\Contract\Payment\ContractPayment;
use App\Models\OperationsCenter\Offer\Offers;
use App\Traits\ApprovalWorkflow\HasApprovalWorkflow;
use App\Traits\HijriDateConversion;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class Contract extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, HijriDateConversion, HasApprovalWorkflow;

    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */
    protected $fillable = [
        'contract_name',
        'contract_number',
        'customer_id',
        'expected_closure_date',
        'contract_start_date',
        'contract_end_date',
        'offer_id',
        'contract_manager_id',
        'relationship_manager_id',
        'contract_status_id',
        'status',
        'created_by',
        'updated_by',

        'contract_type',
        'main_contract_id',

        // اذا كان العقد ملحق
        'supplementary_technical_offer',
        'supplementary_financial_offer',

        'is_private_and_secret',


        'supplement_preamble',
        'supplement_terms',
    ];

    protected $casts = [
        'status'                 => ContractStatus::class,
        'is_private_and_secret'  => 'boolean',

        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];

    protected $hijriDateFields = [
        'expected_closure_date',
        'contract_start_date',
        'contract_end_date',
    ];


    /*
    |--------------------------------------------------------------------------
    | Flow Type
    |--------------------------------------------------------------------------
    */
    protected string $flowType = 'contract';

    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */
    //  كل الدفعات
    public function contractPayments()
    {
        return $this->hasMany(ContractPayment::class);
    }

    public function paidPayments()
    {
        return $this->hasMany(ContractPayment::class)->where('status', PaymentStatus::Paid);
    }

    public function scheduledPayments()
    {
        return $this->hasMany(ContractPayment::class)->where('status', PaymentStatus::Scheduled);
    }

    public function latePayments()
    {
        return $this->hasMany(ContractPayment::class)->where('status', PaymentStatus::Late);
    }

    public function cancelledPayments()
    {
        return $this->hasMany(ContractPayment::class)->where('status', PaymentStatus::Cancelled);
    }

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function contractManager()
    {
        return $this->belongsTo(Employees::class, 'contract_manager_id');
    }

    public function offer()
    {
        return $this->belongsTo(Offers::class, 'offer_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'contract_project')
            ->withPivot('contract_type')
            ->withTimestamps();
    }

    public function attachments()
    {
        return $this->hasMany(ContractAttachment::class);
    }

    public function relationshipManager() //مسؤول العلاقة
    {
        return $this->belongsTo(Employees::class, 'relationship_manager_id');
    }

    public function contract_status()
    {
        return $this->belongsTo(SettingsContractStatus::class);
    }

    public function mainContract()
    {
        return $this->belongsTo(Contract::class, 'main_contract_id');
    }

    // العلاقة للعقود الملحقة
    public function supplementaryContracts()
    {
        return $this->hasMany(Contract::class, 'main_contract_id');
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

    // تحديد كم المدة المتبقية
    public function getTimeRemainingAttribute()
    {
        if (!$this->contract_start_date) {
            return 'غير متاح';
        }

        $sessionDate = Carbon::parse($this->contract_start_date);

        if ($sessionDate->isFuture()) {
            // الحصول على الفرق بصيغة بشرية
            $diff = $sessionDate->diffForHumans(null, false, false, 2); // مثال: "بعد 5 دقائق"

            // استبدال "بعد" بـ "تبقي"
            $diff = str_replace('بعد', 'تبقي', $diff);

            return $diff;
        } else {
            return 'العقد بدأ بالفعل';
        }
    }

    public function getExpectedClosureDateAttribute($value)
    {
        return $this->convertToHijri($value);
    }

    public function getContractStartDateAttribute($value)
    {
        return $this->convertToHijri($value);
    }

    public function getContractEndDateAttribute($value)
    {
        return $this->convertToHijri($value);
    }

    public function getHijriCreatedAtAttribute()
    {
        return Hijri::ShortDate($this->created_at);
    }



    /*
    |============================================================================
    |============================================================================
    |                               Custom methods
    |============================================================================
    |============================================================================
    */
    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created' => 'تم إنشاء العقد',
            'updated' => 'تم تحديث العقد',
            'deleted' => 'تم حذف العقد',
            'restored' => 'تم استعادة العقد',
            'forceDeleted' => 'تم حذف العقد بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('contract')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} العقد";
            });
    }
}
