<?php

namespace App\Models\Hr\Violations;

use App\Enums\Hr\ViolationsPenalties\ViolationAppealStatus;
use App\Models\Hr\Employees\Employees;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViolationAppeal extends Model
{
    use HasFactory;

    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */

    protected $fillable = [
        'violation_id',
        'appeal_reason',
        'response_date',
        'status',
        'response',
        'reviewed_by',
        'reviewed_at',
    ];


    protected $casts = [
        'status'           => ViolationAppealStatus::class,

        'reviewed_at'    => 'datetime',

        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'deleted_at'    => 'datetime',
    ];


    public function violation()
    {
        return $this->belongsTo(Violation::class, 'violation_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(Employees::class, 'reviewed_by');
    }
}
