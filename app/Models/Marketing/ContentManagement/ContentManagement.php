<?php

namespace App\Models\Marketing\ContentManagement;

use App\Enums\Marketing\ContentManagement\ContentStatus;
use App\Enums\Marketing\ContentManagement\MediaType;
use App\Enums\Marketing\ContentManagement\PublicationStatus;
use App\Enums\Marketing\ContentManagement\PublishType;
use App\Enums\Marketing\ContentManagement\RecurringType;
use App\Enums\Shared\WeekDay;
use App\Models\general_setting\Marketing\SettingsContentPillar;
use App\Models\general_setting\Marketing\SettingsContentType;
use App\Models\general_setting\Marketing\SettingsPublishingPattern;
use App\Models\general_setting\Marketing\SettingsContentPurpose;
use App\Models\general_setting\SettingsSocial;
use App\Models\Hr\Employees\Employees;
use App\Models\Marketing\ContentManagement\SocialPublication\SocialPublication;
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

class ContentManagement extends Model
{
    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */

    use HasFactory, SoftDeletes, LogsActivity, HasApprovalWorkflow, BelongsToTenant;
    // use Cachable;

    protected $fillable = [
        'content_type_id',
        'content_text',
        'publishing_pattern_id',
        'content_purpose_id',
        'publication_status',
        'status',
        'media_type',
        'media',
        'publication_date',

        // حقول الجدولة 
        'publish_type',
        'one_time_at',
        'recurring_type',
        'publish_time',
        'start_date',
        'end_date',
        'week_days',
        'month_day',
        'is_active',

        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created'       => 'تم إنشاء محتوى تسويقي جديدة',
            'updated'       => 'تم تحديث محتوى تسويقي موجودة مسبقا',
            'deleted'       => 'تم حذف محتوى تسويقي ',
            'restored'      => 'تم استعادة محتوى تسويقي ',
            'forceDeleted'  => 'تم حذف محتوى تسويقي بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('ContentManagement')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} محتوى تسويقي";
            });
    }


    protected $casts = [
        'publication_status' => PublicationStatus::class,
        'status'             => ContentStatus::class,
        'media_type'         => MediaType::class,

        // حقول الجدولة 
        'publish_type'      => PublishType::class,
        'recurring_type'    => RecurringType::class,
        'week_days'         => 'array',
        'is_active'         => 'boolean',
        'one_time_at'       => 'datetime',
        'publication_date'  => 'date',

        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Flow Type
    |--------------------------------------------------------------------------
    */
    protected string $flowType = 'content';

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

    // نوع المحتوى
    public function contentType()
    {
        return $this->belongsTo(SettingsContentType::class, 'content_type_id');
    }

    // نمط النشر
    public function publishingPattern()
    {
        return $this->belongsTo(SettingsPublishingPattern::class, 'publishing_pattern_id');
    }

    // الهدف من القطعة
    public function contentpurpose()
    {
        return $this->belongsTo(SettingsContentPurpose::class, 'content_purpose_id');
    }

    // pulications
    public function socialPublications()
    {
        return $this->hasMany(SocialPublication::class, 'content_management_id');
    }

    // المنصة
    public function socials()
    {
        return $this->belongsToMany(
            SettingsSocial::class,
            'content_management_socials',
            'content_management_id',
            'social_id'
        )->withTimestamps();
    }

    /*
    |============================================================================
    |============================================================================
    |                               Accessors
    |============================================================================
    |============================================================================
    */
    protected function publishTime(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? Carbon::parse($value)->format('H:i') : null,
        );
    }



    /*
    |============================================================================
    |                               Media Helpers
    |============================================================================
    |
    */

    /*
    |--------------------------------------------------------------------------
    |التحقق مما إذا كان نوع المرفق هو صورة.
    |--------------------------------------------------------------------------
    */
    public function isImage(): bool
    {
        return $this->media_type === MediaType::Image;
    }

    /*
    |--------------------------------------------------------------------------
    | التحقق مما إذا كان نوع المرفق هو فيديو.
    |--------------------------------------------------------------------------
    */
    public function isVideo(): bool
    {
        return $this->media_type === MediaType::Video;
    }
}
