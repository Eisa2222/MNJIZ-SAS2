<?php

namespace App\Models\LegalAffair\PowerOfAttorney;


use Alkoumi\LaravelHijriDate\Hijri;
use App\Enums\LegalAffair\PowerOfAttorney\PowerOfAttorneyStatus;
use App\Models\OperationsCenter\Customer\Customers;
use App\Models\Hr\Employees\Employees;
use App\Models\LegalAffair\Lawsuit\Lawsuit;
use App\Models\User;
use App\Traits\HijriDateConversion;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PowerOfAttorney extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, Cachable, HijriDateConversion;

    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */
    protected $fillable = [
        'power_name',
        'power_number',
        'date_issued',
        'date_expiry',
        'scope',
        'status',
        'file_attachment',
        'notes',

        'created_by',
        'updated_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created'       => 'تم إنشاء الوكالة',
            'updated'       => 'تم تحديث الوكالة',
            'deleted'       => 'تم حذف الوكالة',
            'restored'      => 'تم استعادة الوكالة',
            'forceDeleted'  => 'تم حذف الوكالة بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('power_of_attorney')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} الوكالة";
            });
    }


    protected $casts = [
        'status'          => PowerOfAttorneyStatus::class,

        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];

    protected $hijriDateFields = [
        'date_issued',
        'date_expiry',
    ];


    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */

    public function customers()
    {
        return $this->belongsToMany(Customers::class, 'power_attorney_customers', 'power_attorney_id', 'customer_id')
            ->withPivot('user_id')
            ->withTimestamps();
    }

    public function agents() //الوكلاء
    {
        return $this->belongsToMany(Employees::class, 'power_attorney_agents', 'power_attorney_id', 'employee_id')
            ->withPivot('user_id')
            ->withTimestamps();
    }


    public function lawsuits()
    {
        return $this->belongsToMany(Lawsuit::class, 'lawsuit_power_of_attorneys', 'power_of_attorney_id', 'lawsuit_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }



    public function getHijriDateIssuedAttribute()
    {
        return Hijri::ShortDate($this->date_issued);
    }


    public function getHijriDateExpiryAttribute()
    {
        if (empty($this->date_expiry)) {
            return null;
        }
        return Hijri::ShortDate($this->date_expiry);
    }
}
