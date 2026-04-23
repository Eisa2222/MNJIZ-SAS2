<?php

namespace App\Models\PolicyAgreement;


use App\Models\Hr\CompanyPolicy\CompanyPolicy;
use App\Models\Hr\Employees\Employees;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPolicyAgreement extends Model
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
        'employee_id',
        'company_policy_id',
        'agreed_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'agreed_at' => 'datetime',
    ];


    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */
    public function employee()
    {
        return $this->belongsTo(Employees::class);
    }

    public function companyPolicy()
    {
        return $this->belongsTo(CompanyPolicy::class);
    }
}
