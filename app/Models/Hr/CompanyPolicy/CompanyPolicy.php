<?php

namespace App\Models\Hr\CompanyPolicy;


use App\Models\Hr\Employees\Employees;
use App\Models\PolicyAgreement\UserPolicyAgreement;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CompanyPolicy extends Model
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
        'name',
        'file_path',
        'is_mandatory',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
    ];


    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created'       => 'تم اضافة سياسات و لوائح جديدة',
            'updated'       => 'تم تحديث سياسات و لوائح موجودة مسبقا',
            'deleted'       => 'تم حذف سياسات و لوائح ',
            'restored'      => 'تم استعادة سياسات و لوائح ',
            'forceDeleted'  => 'تم حذف سياسات و لوائح بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('company_policies')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} سياسات و لوائح";
            });
    }

    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */
    public function userAgreements()
    {
        return $this->hasMany(UserPolicyAgreement::class, 'company_policy_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(Employees::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(Employees::class, 'updated_by');
    }
}
