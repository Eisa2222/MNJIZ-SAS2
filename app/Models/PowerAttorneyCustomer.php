<?php

namespace App\Models;

use App\Models\OperationsCenter\Customer\Customers;
use App\Models\judicial_affairs\PowerOfAttorney;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PowerAttorneyCustomer extends Model
{
    use HasFactory;

    protected $fillable = [
        'power_attorney_id',
        'customer_id',
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
     * العلاقة مع نموذج Customer
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(Customers::class);
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
