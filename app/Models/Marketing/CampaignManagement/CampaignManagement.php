<?php

namespace App\Models\Marketing\CampaignManagement;

use App\Enums\Marketing\CampaignManagement\CampaignStatus;
use App\Models\general_setting\Marketing\SettingsCampaignSection;
use App\Models\general_setting\Marketing\SettingsContentPurpose;
use App\Models\general_setting\Marketing\SettingsContentType;
use App\Models\general_setting\Marketing\SettingsDisplayLocation;
use App\Models\general_setting\Marketing\SettingsTargetAudience;
use App\Models\general_setting\SettingsSocial;
use App\Models\Hr\Employees\Employees;
use App\Models\Marketing\CampaignManagement\CampaignResult\CampaignResult;
use App\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CampaignManagement extends Model
{
    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */

    use HasFactory, SoftDeletes, LogsActivity, BelongsToTenant;
    // use Cachable;

    protected $fillable = [
        'campaign_name',
        'content_type_id',
        'campaign_section_id',
        'content_purpose_id',
        'social_id',
        'budget',
        'start_date',
        'end_date',
        'target_audience_id',
        'status',
        'text',

        'created_by',
        'updated_by',
        'deleted_by',
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


    protected $casts = [
        'status'            => CampaignStatus::class,

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
    public function createdBy()
    {
        return $this->belongsTo(Employees::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(Employees::class, 'updated_by');
    }

    public function contentType()
    {
        return $this->belongsTo(SettingsContentType::class, 'content_type_id');
    }

    public function campaignSection()
    {
        return $this->belongsTo(SettingsCampaignSection::class, 'campaign_section_id');
    }

    public function contentPurpose()
    {
        return $this->belongsTo(SettingsContentPurpose::class, 'content_purpose_id');
    }

    public function social()
    {
        return $this->belongsTo(SettingsSocial::class, 'social_id');
    }

    public function targetAudience()
    {
        return $this->belongsTo(SettingsTargetAudience::class, 'target_audience_id');
    }

    public function result()
    {
        return $this->hasOne(CampaignResult::class, 'campaign_id');
    }
}
