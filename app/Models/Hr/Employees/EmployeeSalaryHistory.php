<?php

namespace App\Models\Hr\Employees;

use App\Models\Hr\Employees\Employees;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeSalaryHistory extends Model
{
    use HasFactory;


    /*
    |--------------------------------------------------------------------------
    | fillable
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'employee_id',
        'basic_salary',
        'transportation_allowance',
        'housing_allowance',
        'other_allowances',
        'effective_from',
    ];


    /*
    |--------------------------------------------------------------------------
    | employee
    |--------------------------------------------------------------------------
    */
    public function employee()
    {
        return $this->belongsTo(Employees::class);
    }
}
