<?php

namespace App\Models\Survey;

use App\Enums\Survey\SurveyStatus;
use App\Enums\Survey\SurveyType;
use App\Models\Hr\Employees\Employees;
use App\Tenancy\Concerns\BelongsToTenant;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Survey extends Model
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
        'title',
        'type',
        'description',
        'message_template',
        'status',
        'created_by',
        'updated_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created'       => 'تم إنشاء استبيان جديد',
            'updated'       => 'تم تحديث استبيان موجود مسبقا',
            'deleted'       => 'تم حذف استبيان ',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('Survey')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} استبيان";
            });
    }


    protected $casts = [
        'status'          => SurveyStatus::class,
        'type'            => SurveyType::class,

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
    public function questions()
    {
        return $this->hasMany(SurveyQuestion::class, 'survey_id');
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
