<?php

namespace App\Models\judicial_affairs;

use App\Models\Hr\Employees\Employees;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractApprovalLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'employee_id',
        'action',
        'reason',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employees::class);
    }
}