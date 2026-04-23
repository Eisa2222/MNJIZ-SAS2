<?php

namespace App\Models\judicial_affairs;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Courtroom extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;
    protected $dates = ['deleted_at']; 
    protected $fillable = [
        'court_id', 'name', 'floor', 'capacity', 'notes',
        'created_by', 'updated_by', 'deleted_by'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'capacity'])
            ->useLogName('courtroom')
            ->setDescriptionForEvent(fn(string $eventName) => "The courtroom has been {$eventName}");
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
