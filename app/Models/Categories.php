<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categories extends Model
{
    use HasFactory, SoftDeletes;
    protected $dates = ['deleted_at']; 
    protected $fillable = [
        'name', // حقل لاسم التصنيف
        'status', // حقل للحالة
        'user_id' // حقل للحالة
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
