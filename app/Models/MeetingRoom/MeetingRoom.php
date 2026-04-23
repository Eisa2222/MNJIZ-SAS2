<?php

namespace App\Models\MeetingRoom;

use App\Models\Hr\Employees\Employees;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MeetingRoom extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, Cachable;

    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */
    protected $fillable = [
        'title',
        'hall',
        'date',
        'from_time',
        'to_time',
        'notes',
        'created_by',
        'updated_by',
    ];


    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created'       => 'تم إضافة حجز قاعة جديد',
            'updated'       => 'تم تحديث حجز قاعة موجود مسبقا',
            'deleted'       => 'تم حذف حجز قاعة ',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('meeting-room')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} حجز قاعة";
            });
    }

    protected $casts = [
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'deleted_at'    => 'datetime',
    ];


    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */


    public function createdBy()
    {
        return $this->belongsTo(Employees::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(Employees::class, 'updated_by');
    }

    public function participants()
    {
        return $this->hasMany(Participant::class, 'meeting_id');
    }

    public function getHallNameAttribute()
    {
        return $this->hall === 'big' ? 'القاعة الكبيرة' : 'القاعة الصغيرة';
    }
}
