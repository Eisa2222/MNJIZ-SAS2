<?php

namespace App\Models\judicial_affairs;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProjectEmployee extends Model
{
    use HasFactory,  LogsActivity;

    protected $table = 'project_employee';

    protected $fillable = [
        'project_id',
        'employee_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created' => 'تم إنشاء فريق المشروع',
            'updated' => 'تم تحديث فريق المشروع',
            'deleted' => 'تم حذف فريق المشروع',
            'restored' => 'تم استعادة فريق المشروع',
            'forceDeleted' => 'تم حذف فريق المشروع بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('project_employee')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} فريق المشروع";
            });
    }
}
