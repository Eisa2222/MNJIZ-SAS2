<?php

namespace App\Models\OperationsCenter\Customer;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Enums\OperationsCenter\Customer\CustomerType;
use App\Models\Authorization;
use App\Models\general_setting\SettingsClientStatus;
use App\Models\general_setting\SettingsCountry;
use App\Models\general_setting\SettingsMarketingChannel;
use App\Models\general_setting\SettingsRegion;
use App\Models\general_setting\SettingsSector;
use App\Models\general_setting\SettingsSocial;
use App\Models\Hr\Employees\Employees;
use App\Models\judicial_affairs\Project;
use App\Models\LegalAffair\Lawsuit\Lawsuit;
use App\Models\LegalAffair\PowerOfAttorney\PowerOfAttorney;
use App\Models\OperationsCenter\Contract\Contract;
use App\Models\OperationsCenter\Offer\Offers;
use App\Models\PowerAttorneyCustomer;
use App\Models\Survey\SurveyResponse;
use App\Models\User;
use App\Tenancy\Concerns\BelongsToTenant;
use Beta\Microsoft\Graph\Model\Customer;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Customers extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, Cachable, BelongsToTenant;

    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */

    protected $fillable = [
        'name',
        'title',
        'nationality_id',
        'status_id',
        'contact_number',
        'email',
        'address',
        'relationship_manager_id',
        'marketing_channel_id',
        'detailed_marketing_channel_id',
        'sector_id',
        'parent_customer_id',
        'social_media_id',
        'civil_registry_number',
        'commercial_registration_number',
        'unified_number',
        'customer_type',
        'department_id',
        'created_by',
        'updated_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created' => 'تم إنشاء العميل',
            'updated' => 'تم تحديث العميل',
            'deleted' => 'تم حذف العميل',
            'restored' => 'تم استعادة العميل',
            'forceDeleted' => 'تم حذف العميل بشكل نهائي',

        ];

        return LogOptions::defaults()
            ->logAll() // تسجيل جميع الحقول
            ->useLogName('customer')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} العميل";
            });
    }

    protected $casts = [
        'customer_type'  => CustomerType::class,

        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];

    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */

    public function nationality()
    {
        return $this->belongsTo(SettingsCountry::class, 'nationality_id');
    }

    public function status()
    {
        return $this->belongsTo(SettingsClientStatus::class, 'status_id');
    }

    public function region()
    {
        return $this->belongsTo(SettingsRegion::class, 'address');
    }

    public function relationshipManager()
    {
        return $this->belongsTo(Employees::class, 'relationship_manager_id');
    }

    public function marketingChannel()
    {
        return $this->belongsTo(SettingsMarketingChannel::class, 'marketing_channel_id');
    }

    public function detailedMarketingChannel()
    {
        return $this->belongsTo(Employees::class, 'detailed_marketing_channel_id');
    }

    public function sector()
    {
        return $this->belongsTo(SettingsSector::class, 'sector_id');
    }

    public function parentCustomer()
    {
        return $this->belongsTo(Customers::class, 'parent_customer_id');
    }

    public function socialMedia()
    {
        return $this->belongsTo(SettingsSocial::class, 'social_media_id');
    }

    public function authorizations()
    {
        return $this->hasMany(Authorization::class, 'customer_id');
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class, 'customer_id');
    }

    public function offers()
    {
        return $this->hasMany(Offers::class, 'customer_id');
    }

    public function powerOfAttorneys()
    {
        return $this->belongsToMany(
            PowerOfAttorney::class,
            'power_attorney_customers',
            'customer_id',
            'power_attorney_id'
        )->withPivot('user_id')->withTimestamps();
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }


    public function lawsuitsAsPlaintiff()
    {
        return $this->belongsToMany(
            Lawsuit::class,
            'lawsuit_plaintiffs',
            'plaintiff_id',
            'lawsuit_id'
        )->where('lawsuit_plaintiffs.plaintiff_type', 'App\\Models\\OperationsCenter\\Customer');
    }

    public function lawsuitsAsDefendant()
    {
        return $this->belongsToMany(
            Lawsuit::class,
            'lawsuit_defendants',
            'defendant_id',
            'lawsuit_id'
        )->where('lawsuit_defendants.defendant_type', 'App\\Models\\OperationsCenter\\Customer');
    }

    public function surveyResponses()
    {
        return $this->hasMany(SurveyResponse::class, 'customer_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }


    /*
    |============================================================================
    |============================================================================
    |                               Accessors
    |============================================================================
    |============================================================================
    */
    public function getHijriCreatedAtAttribute()
    {
        return Hijri::ShortDate($this->created_at);
    }
}
