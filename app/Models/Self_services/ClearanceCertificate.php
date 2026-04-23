<?php

namespace App\Models\Self_services;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Enums\SelfServices\ClearanceCertificateStatus;
use App\Models\User;
use App\Traits\ApprovalWorkflow\HasApprovalWorkflow;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClearanceCertificate extends Model
{
    use HasFactory ,SoftDeletes , HasApprovalWorkflow;


    protected $fillable = [
        'user_id',
        'status',
        'request_date',
        'reason',
        'notes',
        'admin_notes',
        'processed_by',

    ];


        // <-- إضافة Casts و flowType
    protected $casts = [
        'status' => ClearanceCertificateStatus::class,
        'request_date' => 'date',
    ];



      /*
      |--------------------------------------------------------------------------
      | Flow Type
      |--------------------------------------------------------------------------
      */
      protected string $flowType = 'clearance_certificate';


    /*
    |--------------------------------------------------------------------------
    | user requesting the clearance certificate
    |--------------------------------------------------------------------------
    */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | user who approved the clearance certificate
    |--------------------------------------------------------------------------
    */
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /*
    |--------------------------------------------------------------------------
    | get created at
    |--------------------------------------------------------------------------
    */
    public function getHijriCreatedAtAttribute()
    {
        return Hijri::ShortDate($this->created_at);
    }
}
