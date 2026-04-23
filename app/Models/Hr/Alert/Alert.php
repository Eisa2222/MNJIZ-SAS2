<?php

namespace App\Models\Hr\Alert;

use App\Enums\Hr\Alert\AlertStatus;
use App\Enums\Hr\Alert\AlertType;
use App\Models\Hr\Employees\Employees;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Alert extends Model
{
    use HasFactory, LogsActivity, Cachable;

    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */
    protected $fillable = [
        'employee_id',
        'type',
        'status',
        'updated_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created'       => 'تم اضافة تنبيه جديدة',
            'updated'       => 'تم التعامل مع  تنبيه موجودة مسبقا',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('settings')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} تنبيه";
            });
    }


    protected $casts = [
        'type'        => AlertType::class,
        'status'      => AlertStatus::class,
    ];

    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */
    public function employee()
    {
        return $this->belongsTo(Employees::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(Employees::class, 'updated_by');
    }
}
