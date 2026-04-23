<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;
    protected $dates = ['deleted_at']; 
    protected $fillable = [
        'name', 'id_number', 'commercial_record', 'address', 'city', 'phone',
        'landline', 'email', 'nationality', 'password', 'status', 'notes', 'image'
    ];

    protected $hidden = [
        'password',
    ];
}
