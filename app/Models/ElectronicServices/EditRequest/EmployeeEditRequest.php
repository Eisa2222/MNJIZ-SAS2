<?php

namespace App\Models\ElectronicServices\EditRequest;

use App\Enums\Hr\EditRequest\EditRequestStatus;
use App\Models\Hr\Employees\Employees;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class EmployeeEditRequest extends Model
{
    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */
    use HasFactory,  LogsActivity;
    use Cachable;


    protected $fillable = [
        'employee_id',
        'notes',
        'status',
        'updated_by'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created'       => 'تم إنشاء طلب تعديل البيانات الشخصية جديدة',
            'updated'       => 'تم تحديث طلب تعديل البيانات الشخصية موجودة مسبقا',
            'deleted'       => 'تم حذف طلب تعديل البيانات الشخصية ',
            'restored'      => 'تم استعادة طلب تعديل البيانات الشخصية ',
            'forceDeleted'  => 'تم حذف طلب تعديل البيانات الشخصية بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('settings')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} طلب تعديل البيانات الشخصية";
            });
    }

    protected $casts = [
        'status'      => EditRequestStatus::class,

        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];


    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */
    public function fields()
    {
        return $this->hasMany(EmployeeEditRequestField::class, 'edit_request_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employees::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(Employees::class, 'updated_by');
    }
}
