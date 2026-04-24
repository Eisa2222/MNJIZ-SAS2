<?php

namespace App\Models\Survey;

use App\Enums\Survey\SurveyResponse\SurveyResponseStatus;
use App\Models\Hr\Employees\Employees;
use App\Models\OperationsCenter\Customer\Customers;
use App\Tenancy\Concerns\BelongsToTenant;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class SurveyResponse extends Model
{
    use HasFactory, Cachable, BelongsToTenant;

    protected $fillable = [
        'token',
        'survey_id',
        'customer_id',
        'status',
        'completed_at',
        'ip_address',
        'user_agent'
    ];



    protected $casts = [
        'status'        => SurveyResponseStatus::class,
        'expires_at'    => 'datetime',
        'completed_at'  => 'datetime'
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->token) {
                $model->token = Str::uuid();
            }
        });
    }

    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */
    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customers::class);
    }

    public function answers()
    {
        return $this->hasMany(SurveyAnswer::class, 'survey_response_id');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function getPublicUrl(): string
    {
        return route('survey.public', ['token' => $this->token]);
    }
}