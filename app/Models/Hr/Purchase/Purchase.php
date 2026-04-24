<?php

namespace App\Models\Hr\Purchase;

use App\Models\general_setting\SettingsPurchaseCategory;
use App\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory ,SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'item_name',
        'purchase_category_id',
        'item_quantity',
        'item_price',
        'item_description',
        'invoice_id',
    ];


    /*
    |--------------------------------------------------------------------------
    | invoice
    |--------------------------------------------------------------------------
    */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }



    /*
    |--------------------------------------------------------------------------
    | Categories
    |--------------------------------------------------------------------------
    */
    public function category()
    {
        return $this->belongsTo(SettingsPurchaseCategory::class, 'purchase_category_id');
    }
}
