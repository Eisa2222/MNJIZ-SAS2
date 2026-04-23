<?php

namespace App\Models\judicial_affairs;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Judge extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;
    protected $dates = ['deleted_at']; 
    protected $fillable = [
        'first_name', 'last_name', 'full_name', 'court_id', 'phone', 'email', 'notes',
        'created_by', 'updated_by', 'deleted_by'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['first_name', 'last_name', 'phone'])
            ->useLogName('judge')
            ->setDescriptionForEvent(fn(string $eventName) => "The judge has been {$eventName}");
    }


    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }
}
