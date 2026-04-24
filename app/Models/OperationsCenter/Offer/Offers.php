<?php

namespace App\Models\OperationsCenter\Offer;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Enums\OperationsCenter\Offer\OfferStatus;
use App\Models\OperationsCenter\Customer\Customers;
use App\Models\Hr\Employees\Employees;
use App\Models\OperationsCenter\Contract\Contract;
use App\Tenancy\Concerns\BelongsToTenant;
use App\Traits\ApprovalWorkflow\HasApprovalWorkflow;
use Carbon\Carbon;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Offers extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, HasApprovalWorkflow, BelongsToTenant;

    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */
    protected $fillable = [
        'offer_number',
        'offer_name',
        'customer_id',
        'relationship_manager_id',
        'start_date',
        'status',
        'technical_offer',   // العرض الفني
        'financial_offer',   // العرض المالي
        'pdf_path',
        'is_private_and_secret',

        'created_by',
        'updated_by',
    ];


    protected $casts = [
        'status'                 => OfferStatus::class,
        'start_date'             => 'date',
        'is_private_and_secret'  => 'boolean',

        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Flow Type
    |--------------------------------------------------------------------------
    */
    protected string $flowType = 'offer';


    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */
    public function contracts()
    {
        return $this->hasMany(Contract::class, 'offer_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function relationshipManager() //مسؤول العلاقة
    {
        return $this->belongsTo(Employees::class, 'relationship_manager_id');
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
    // المدة المتبقية
    protected function timeRemaining(): Attribute
    {
        return Attribute::make(
            get: fn() => is_null($this->start_date)
                ? 'غير متاح'
                : (
                    $this->start_date->isFuture()
                    ? 'تبقى ' . $this->start_date->diffForHumans(null, true, false, 2)
                    : 'العرض بدأ بالفعل'
                )
        );
    }

    protected function hijriStartDate(): Attribute
    {
        return Attribute::make(
            get: function ($value, array $attributes) {
                // no start_date → null
                if (is_null($this->start_date)) {
                    return null;
                }
                // already Hijri?
                if ($this->isHijriDate($this->start_date)) {
                    return $this->start_date;
                }
                // convert Gregorian → Hijri
                return Hijri::ShortDate($this->start_date);
            }
        );
    }

    // تحويل لميلادي عند الاضافة
    protected function startDate(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                // إذا كان التاريخ هجرياً، نقسمه ثم نحوله
                if ($this->isHijriDate($value)) {
                    [$year, $month, $day] = explode('-', $value);
                    return Hijri::DateToGregorianFromDMY($day, $month, $year);
                }

                return Carbon::parse($value)->format('Y-m-d');
            }
        );
    }

    /*
    |============================================================================
    |============================================================================
    |                               Custom methods
    |============================================================================
    |============================================================================
    */
    // Log activity
    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created'       => 'تم إنشاء العرض',
            'updated'       => 'تم تحديث العرض',
            'deleted'       => 'تم حذف العرض',
            'restored'      => 'تم استعادة العرض',
            'forceDeleted'  => 'تم حذف العرض بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('offer')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} العرض";
            });
    }

    public function isHijriDate($date)
    {
        return preg_match('/^(13|14|15)\d{2}-\d{2}-\d{2}$/', $date);
    }
}
