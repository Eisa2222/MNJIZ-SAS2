<?php

namespace App\Models\Hr\Purchase;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'invoice_number',
        'invoice_date',
        'total_amount',
        'attachment',
        'user_id'
    ];
    

    /*
    |--------------------------------------------------------------------------
    | user create
    |--------------------------------------------------------------------------
    */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    
    /*
    |--------------------------------------------------------------------------
    | purchases
    |--------------------------------------------------------------------------
    */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
