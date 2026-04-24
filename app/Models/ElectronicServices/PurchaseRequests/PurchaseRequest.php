<?php

namespace App\Models\ElectronicServices\PurchaseRequests;


use App\Enums\ElectronicServices\PurchaseRequests\PurchaseRequestsStatus;
use App\Models\general_setting\SettingsPurchaseCategory;
use App\Models\Hr\Employees\Employees;
use App\Tenancy\Concerns\BelongsToTenant;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PurchaseRequest extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, Cachable, BelongsToTenant;

    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */
    protected $fillable = [
        'employee_id',
        'item_name',
        'item_description',
        'item_quantity',
        'status',
        'rejection_reason',
        'processed_at',
        'purchase_category_id',
        'created_by',
        'updated_by',
    ];


    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created'       => 'تم إنشاء طلب مشتريات جديد',
            'updated'       => 'تم تحديث طلب مشتريات موجود مسبقا',
            'deleted'       => 'تم حذف طلب مشتريات ',
            'restored'      => 'تم استعادة طلب مشتريات ',
            'forceDeleted'  => 'تم حذف طلب مشتريات بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('settings')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} طلب مشتريات";
            });
    }


    protected $casts = [
        'status'          => PurchaseRequestsStatus::class,

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
    public function employee()
    {
        return $this->belongsTo(Employees::class);
    }

    public function category()
    {
        return $this->belongsTo(SettingsPurchaseCategory::class, 'purchase_category_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(Employees::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(Employees::class, 'updated_by');
    }
}
