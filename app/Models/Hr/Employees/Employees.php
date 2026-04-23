<?php

namespace App\Models\Hr\Employees;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Enums\Hr\Employee\ContractType;
use App\Enums\Hr\Employee\InsuranceStatus;
use App\Enums\Hr\Employee\KnowledgeArea;
use App\Enums\Hr\Employee\LicenseType;
use App\Enums\Hr\Employee\QualificationDegree;
use App\Enums\Hr\Employee\TrialPeriod;
use App\Models\ElectronicServices\Custody\Request\CustodyRequest;
use App\Models\ElectronicServices\LeaveRequests\LeaveRequest;
use App\Models\OperationsCenter\Customer\Customers;
use App\Models\general_setting\SettingsBanks;
use App\Models\general_setting\SettingsCountry;
use App\Models\general_setting\SettingsHRClassification;
use App\Models\general_setting\SettingsHrStatus;
use App\Models\Hr\LeaveBalance;
use App\Models\judicial_affairs\Document;
use App\Models\judicial_affairs\Project;
use App\Models\LegalAffair\Lawsuit\Lawsuit;
use App\Models\LegalAffair\PowerOfAttorney\PowerOfAttorney;
use App\Models\OperationsCenter\Contract\Contract;
use App\Models\OperationsCenter\Offer\Offers;
use App\Models\OrganizationCenter\Tasks\Task\Task;
use App\Models\PowerAttorneyAgent;
use App\Models\User;
use App\Tenancy\Concerns\BelongsToTenant;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class Employees extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, HasRoles, Cachable, BelongsToTenant;

    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */
    protected $fillable = [
        'name',
        'nickname',
        'nationality',
        'gender',
        'id_number',
        'personal_email',
        'work_email',
        'mobile',
        'address',
        'birth_date',
        'job_title',
        'license_type',
        'insurance_status',
        'contract_start_date',
        'contract_end_date',
        'training_end_date',
        'work_license_end_date',
        'basic_salary',
        'transportation_allowance',
        'housing_allowance',
        'other_allowances',
        'training_number',
        'national_number',
        'qualification_degree',
        'bio',
        'vacation_balance',
        'business_card',
        'knowledge_area',
        'profile_picture',
        'background_image',
        'resume',
        'qualification_certificate',
        'contract_attachment',
        'id_attachment',
        'bank_account_attachment',
        'national_address_attachment',
        'signature',
        'additional_attachments',
        'user_id',
        'hr_classification_id',
        'hr_status_id',
        'created_by',
        'bank_account_type',
        'iban',
        'has_insurance',        // هل لديه تأمينات
        'insurance_percentage', // نسبة التأمينات

        'contract_type',
        'law_license_number',
        'law_license_end_date',
        'trial_period',
    ];

    protected $dates = ['deleted_at'];


    protected $casts = [
        'additional_attachments'    => 'array',
        'contract_start_date'       => 'date',
        'contract_end_date'         => 'date',
        'law_license_end_date'      => 'date',
        'training_end_date'         => 'date',
        'work_license_end_date'     => 'date',
        'birth_date'                => 'date',
        // enum fields
        'license_type'              => LicenseType::class,
        'contract_type'             => ContractType::class,
        'trial_period'              => TrialPeriod::class,
        'insurance_status'          => InsuranceStatus::class,
        'qualification_degree'      => QualificationDegree::class,
        'knowledge_area'            => KnowledgeArea::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created'       => 'تم إنشاء الموظف',
            'updated'       => 'تم تحديث الموظف',
            'deleted'       => 'تم حذف الموظف',
            'restored'      => 'تم استعادة الموظف',
            'forceDeleted'  => 'تم حذف الموظف بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll() // تسجيل جميع الحقول
            ->useLogName('employees')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} الموظف";
            });
    }

    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */
    public function bank_name()
    {
        return $this->belongsTo(SettingsBanks::class, 'bank_account_type');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function offers()
    {
        return $this->hasMany(Offers::class, 'relationship_manager_id');
    }

    public function powerAttorneyAgents()
    {
        return $this->hasMany(PowerAttorneyAgent::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    public function manager()
    {
        return $this->belongsTo(Employees::class, 'manager_id');
    }

    public function subordinates()
    {
        return $this->hasMany(Employees::class, 'manager_id');
    }

    public function hrClassification()
    {
        return $this->belongsTo(SettingsHRClassification::class, 'hr_classification_id');
    }

    public function hrStatus()
    {
        return $this->belongsTo(SettingsHrStatus::class, 'hr_status_id');
    }

    public function country()
    {
        return $this->belongsTo(SettingsCountry::class, 'nationality');
    }

    // قناة التسويقية التفصيلية
    public function customers()
    {
        return $this->hasMany(Customers::class, 'detailed_marketing_channel_id');
    }

    // مسؤول العلاقة
    public function customersRelationshipManager()
    {
        return $this->hasMany(Customers::class, 'relationship_manager_id');
    }

    // إذا كانت العلاقة كثير إلى كثير
    public function powerOfAttorneys()
    {
        return $this->belongsToMany(PowerOfAttorney::class, 'power_attorney_agents');
    }

    public function assignedTasks()
    {
        return $this->belongsToMany(Task::class, 'task_assignees');
    }

    public function lawsuits()
    {
        return $this->hasMany(Lawsuit::class, 'attorney_id');
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id', 'id');
    }

    public function custodies()
    {
        return $this->hasMany(CustodyRequest::class, 'employee_id');
    }

    /*
    |============================================================================
    |============================================================================
    |                               Accessors
    |============================================================================
    |============================================================================
    */
    // لعرض لقب الموظف بدلا من اسمه
    public function getNameAttribute($value)
    {
        return $this->attributes['nickname']
            ? $this->attributes['nickname']
            : $value;
    }

    // للوصول للاسم الأصلي عند الحاجة
    public function getRawNameAttribute()
    {
        return $this->getRawOriginal('name');
    }

    // سجلّات تاريخ الرواتب
    public function salaryHistories()
    {
        return $this->hasMany(EmployeeSalaryHistory::class, 'employee_id');
    }

    // انتهي قبل كم يوم
    public function getContractStatusAttribute()
    {
        if (!$this->contract_end_date) {
            return null;
        }

        $daysLeft = now()->diffInDays($this->contract_end_date, false);

        if ($daysLeft < 0) {
            return 'انتهى قبل ' . abs($daysLeft) . ' يوم';
        }

        return 'ساري';
    }

    // التحقق من انتهاء العقد
    public function getIsContractExpiredAttribute()
    {
        if (!$this->contract_end_date) {
            return false;
        }

        return now() > $this->contract_end_date;
    }

    /*
    |============================================================================
    |============================================================================
    |                           Scope Methods
    |============================================================================
    |============================================================================
    */
    public function scopeActive($query)
    {
        return $query->whereHas('user', function ($query) {
            $query->where('status', 'active');
        });
    }
    /*
    |============================================================================
    |============================================================================
    |                          Custom Methods
    |============================================================================
    |============================================================================
    */
    public function getHijriCreatedAtAttribute()
    {
        return Hijri::ShortDate($this->created_at);
    }
}
