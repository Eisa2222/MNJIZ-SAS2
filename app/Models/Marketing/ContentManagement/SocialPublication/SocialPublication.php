<?php

namespace App\Models\Marketing\ContentManagement\SocialPublication;

use App\Enums\Marketing\ContentManagement\SocialPublication\SocialPublicationStatus;
use App\Models\Marketing\ContentManagement\ContentManagement;
use App\Tenancy\Concerns\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SocialPublication extends Model
{
    use HasFactory, LogsActivity, BelongsToTenant;

    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */
    protected $fillable = [
        'content_management_id',
        'platform',
        'scheduled_for',     // جديد
        'status',            // جديد
        'platform_post_id',
        'published_at',
        'error_message',     // جديد
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created'       => 'تم نشر محتوى تسويقي  على منصة التواصل الاجتماعي',
            'updated'       => 'تم تحديث محتوى تسويقي  على منصة التواصل الاجتماعي',
            'deleted'       => 'تم حذف محتوى تسويقي  من منصة التواصل الاجتماعي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('ContentManagement')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} محتوى تسويقي";
            });
    }


    protected $casts = [
        'published_at'    => 'datetime',
        'status'          => SocialPublicationStatus::class
    ];



    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */
    public function contentManagement(): BelongsTo
    {
        return $this->belongsTo(ContentManagement::class);
    }


    public function scopePlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    public function getPostUrlAttribute(): string
    {
        switch ($this->platform) {
            case 'linkedin':
                return "https://www.linkedin.com/feed/update/{$this->platform_post_id}";
            case 'x':
                return "https://twitter.com/i/web/status/{$this->platform_post_id}";
            default:
                return '';
        }
    }


    /*
    |============================================================================
    |============================================================================
    |                            Scope
    |============================================================================
    |============================================================================
    */
    public function scopeReadyToPublish($query)
    {
        return $query->where('status', 'pending')
            ->where('scheduled_for', '<=', Carbon::now())
            ->whereHas('contentManagement', function ($q) {
                $q->where('status', 'approved')
                    ->where('is_active', 1);
            });
    }
}
