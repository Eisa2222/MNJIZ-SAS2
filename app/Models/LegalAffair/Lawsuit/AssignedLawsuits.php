<?php

namespace App\Models\LegalAffair\Lawsuit;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AssignedLawsuits extends Pivot
{
    use HasFactory, LogsActivity;
    protected $table = 'assigned_lawsuits';
    protected $fillable = [
        'lawsuit_id',
        'assigned_to',
        'user_accepted_id',
        'status'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created'           => 'تم اصافة المكلفين',
            'updated'           => 'تم تحديث المكلفين',
            'deleted'           => 'تم حذف المكلفين',
            'restored'          => 'تم استعادة المكلفين',
            'forceDeleted'      => 'تم حذف المكلفين بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('project')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} المكلفين";
            });
    }


    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to', 'id');
    }


    public function userAccepted()
    {
        return $this->belongsTo(User::class, 'user_accepted_id', 'id');
    }
}
