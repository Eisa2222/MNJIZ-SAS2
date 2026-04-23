<?php

namespace App\Models;

use App\Models\Hr\Employees\Employees;
use App\Models\judicial_affairs\PowerOfAttorney;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PowerAttorneyAgent extends Model
{
    use HasFactory;

    protected $fillable = [
        'power_attorney_id',
        'employee_id',
        'user_id',
    ];

    /**
     * العلاقة مع نموذج PowerAttorney
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function powerAttorney()
    {
        return $this->belongsTo(PowerOfAttorney::class);
    }

    /**
     * العلاقة مع نموذج Employee
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee()
    {
        return $this->belongsTo(Employees::class);
    }

    /**
     * العلاقة مع نموذج User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
