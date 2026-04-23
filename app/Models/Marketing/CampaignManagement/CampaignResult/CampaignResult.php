<?php

namespace App\Models\Marketing\CampaignManagement\CampaignResult;

use App\Models\Hr\Employees\Employees;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CampaignResult extends Model
{
    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */
    use HasFactory, LogsActivity;

    protected $fillable = [
        'campaign_id',
        'report_date',
        'spend',
        'impressions',
        'clicks',
        'ctr',
        'cpc',
        'conversions',
        'conversion_value',
        'roas',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'report_date'      => 'date',
        'spend'            => 'float',
        'impressions'      => 'integer',
        'clicks'           => 'integer',
        'ctr'              => 'float',
        'cpc'              => 'float',
        'conversions'      => 'integer',
        'conversion_value' => 'float',
        'roas'             => 'float',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created'       => 'تم إنشاء حملة أعلانية جديدة',
            'updated'       => 'تم تحديث حملة أعلانية موجودة مسبقا',
            'deleted'       => 'تم حذف حملة أعلانية ',
            'restored'      => 'تم استعادة حملة أعلانية ',
            'forceDeleted'  => 'تم حذف حملة أعلانية بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('ContentManagement')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} حملة أعلانية";
            });
    }

    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */
    public function createdBy()
    {
        return $this->belongsTo(Employees::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(Employees::class, 'updated_by');
    }
}
