<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Support extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;
    protected $dates = ['deleted_at']; 
    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created' => 'تم إنشاء التذكرة',
            'updated' => 'تم تحديث التذكرة',
            'deleted' => 'تم حذف التذكرة',
            'restored' => 'تم استعادة التذكرة',
            'forceDeleted' => 'تم حذف التذكرة بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('ticket_number')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} التذكرة";
            });
    }

    protected $fillable = [
        'ticket_number',
        'user_id',
        'ticket_classification',
        'title',
        'priority',
        'notes',
        'attachment',
        'reply',
        'status',
        'processed_by'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function processedBy()
    {
        return $this->belongsTo(User::class ,'processed_by');
    }



    public function replies()
    {
        return $this->hasMany(ReplySupport::class);
    }
}
