<?php

namespace App\Models\ElectronicServices\EditRequest;

use App\Enums\Hr\EditRequest\FieldName;
use App\Enums\Hr\EditRequest\RequestFieldStatus;
use App\Models\Hr\Employees\Employees;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeEditRequestField extends Model
{
    use HasFactory;

    protected $fillable = [
        'edit_request_id',
        'field_name',
        'old_value',
        'new_value',
        'status',
        'reviewed_by'
    ];

    protected $casts = [
        'status'            => RequestFieldStatus::class,
        'field_name'        => FieldName::class,

    ];


    public function reviewedBy()
    {
        return $this->belongsTo(Employees::class, 'reviewed_by');
    }

    public function request()
    {
        return $this->belongsTo(EmployeeEditRequest::class, 'edit_request_id');
    }
}
