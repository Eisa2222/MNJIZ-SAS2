<?php

namespace App\Models\Survey;

use App\Models\Hr\Employees\Employees;
use App\Tenancy\Concerns\BelongsToTenant;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SurveyQuestion extends Model
{
    use HasFactory, LogsActivity, SoftDeletes, Cachable, BelongsToTenant;

    protected $fillable = [
        'survey_id',
        'question_text',
        'created_by',
        'updated_by',
    ];


    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created'       => 'تم إنشاء اسئلة استبيان جديدة',
            'updated'       => 'تم تحديث اسئلة استبيان موجودة مسبقا',
            'deleted'       => 'تم حذف اسئلة استبيان ',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('Survey')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} اسئلة استبيان";
            });
    }


    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];



    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */
    public function survey()
    {
        return $this->belongsTo(Survey::class, 'survey_id');
    }

    public function options()
    {
        return $this->hasMany(SurveyQuestionOption::class, 'question_id');
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
