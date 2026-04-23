<?php

namespace App\Models\Survey;

use App\Models\Hr\Employees\Employees;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SurveyQuestionOption extends Model
{
    use HasFactory, LogsActivity, SoftDeletes, Cachable;


    protected $fillable = [
        'question_id',
        'option_text',
        'created_by',
        'updated_by',
    ];


    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created'       => 'تم اضافة اجابات استبيان جديدة',
            'updated'       => 'تم تحديث اجابات استبيان موجودة مسبقا',
            'deleted'       => 'تم حذف اجابات استبيان ',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('SurveyQuestionOption')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} اجابات استبيان";
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
    public function question()
    {
        return $this->belongsTo(SurveyQuestion::class, 'question_id');
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
