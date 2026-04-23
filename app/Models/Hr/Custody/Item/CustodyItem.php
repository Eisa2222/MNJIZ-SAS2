<?php

namespace App\Models\Hr\Custody\Item;

use App\Enums\Hr\Custody\Item\CustodyItemStatus;
use App\Enums\Hr\Custody\Item\CustodyUseStatus;
use App\Models\ElectronicServices\Custody\Log\CustodyLog;
use App\Models\ElectronicServices\Custody\Request\CustodyRequest;
use App\Models\general_setting\HR\Custody\SettingsAssetCategory;
use App\Models\general_setting\HR\Custody\SettingsStorageLocation;
use App\Models\Hr\Employees\Employees;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CustodyItem extends Model
{
    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */

    use HasFactory, SoftDeletes, LogsActivity;
    // use Cachable;

    protected $fillable = [
        'name',
        'serial_number',
        'price',
        'asset_category_id',
        'storage_location_id',
        'description',
        'custody_status',
        'use_status',

        'created_by',
        'updated_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created'       => 'تم إنشاء اصل جديد',
            'updated'       => 'تم تحديث اصل موجود مسبقا',
            'deleted'       => 'تم حذف اصل ',
            'restored'      => 'تم استعادة اصل ',
            'forceDeleted'  => 'تم حذف اصل بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('settings')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} اصل";
            });
    }


    protected $casts = [
        'custody_status'    => CustodyItemStatus::class,
        'use_status'        => CustodyUseStatus::class,

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
    public function logs()
    {
        return $this->hasManyThrough(
            CustodyLog::class,
            CustodyRequest::class,
            'custody_item_id',
            'custody_request_id'
        )->latest();
    }

    public function assetCategory()
    {
        return $this->belongsTo(SettingsAssetCategory::class, 'asset_category_id');
    }

    public function storageLocation()
    {
        return $this->belongsTo(SettingsStorageLocation::class, 'storage_location_id');
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
