<?php

namespace App\Models\ElectronicServices\Custody\Log;

use App\Enums\ElectronicServices\Custody\Log\CustodyLogAction;
use App\Models\ElectronicServices\Custody\Request\CustodyRequest;
use App\Models\Hr\Employees\Employees;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CustodyLog extends Model
{
    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */

    use HasFactory, LogsActivity;
    // use Cachable;

    protected $fillable = [
        'custody_request_id',
        'action',
        'log_date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created'       => 'تم إنشاء سجل جديد للعهدة ',
            'updated'       => 'تم تحديث سجل العهدة ',
            'deleted'       => 'تم حذف سجل العهدة ',
            'restored'      => 'تم استعادة سجل العهدة ',
            'forceDeleted'  => 'تم حذف سجل العهدة بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('settings')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} سجل العهدة";
            });
    }


    protected $casts = [
        'action'        => CustodyLogAction::class,

        'log_date'      => 'datetime',
        'deleted_at'    => 'datetime',
    ];


    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */

    public function request()
    {
        return $this->belongsTo(CustodyRequest::class, 'custody_request_id');
    }
}
