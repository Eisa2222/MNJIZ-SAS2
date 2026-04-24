<?php

namespace App\Models\Survey;

use App\Tenancy\Concerns\BelongsToTenant;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyAnswer extends Model
{
    use HasFactory,  Cachable, BelongsToTenant;

    protected $fillable = [
        'survey_response_id',
        'question_id',
        'option_id',
        'answer_text'
    ];

    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */
    public function surveyResponse()
    {
        return $this->belongsTo(SurveyResponse::class);
    }

    public function question()
    {
        return $this->belongsTo(SurveyQuestion::class);
    }

    public function option()
    {
        return $this->belongsTo(SurveyQuestionOption::class);
    }
}
