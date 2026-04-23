<?php

namespace App\Models;

use Beta\Microsoft\Graph\Model\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Authorization extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'name',
        'id_number',
        'phone',
        'email',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}