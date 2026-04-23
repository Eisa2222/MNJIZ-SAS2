<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AssignedSession extends Pivot
{
    use HasFactory , LogsActivity ;
    protected $table = 'assigned_sessions';

    protected $fillable = [
        'session_id',
        'assigned_to',
        'user_added_id',
        // أي حقول إضافية
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created' => 'تم اصافة المكلفين',
            'updated' => 'تم تحديث المكلفين',
            'deleted' => 'تم حذف المكلفين',
            'restored' => 'تم استعادة المكلفين',
            'forceDeleted' => 'تم حذف المكلفين بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('project')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} المكلفين";
            });
    }
}
