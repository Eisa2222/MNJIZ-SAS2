<?php

namespace App\Models\LegalAffair\Lawsuit;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Enums\LegalAffair\Lawsuit\LawsuitStatus;
use App\Models\OperationsCenter\Customer\Customers;
use App\Models\general_setting\SettingsCategories;
use App\Models\general_setting\SettingsDepartmentContractCase;
use App\Models\general_setting\SettingsEntity;
use App\Models\general_setting\SettingsLawsuitsType;
use App\Models\general_setting\SettingsMainCourt;
use App\Models\general_setting\SettingsRegion;
use App\Models\Hr\Employees\Employees;
use App\Models\judicial_affairs\Project;
use App\Models\LegalAffair\Lawsuit\Note\LawsuitNote;
use App\Models\LegalAffair\Opponent\Opponent;
use App\Models\LegalAffair\PowerOfAttorney\PowerOfAttorney;
use App\Models\LegalAffair\Session\Session;
use App\Models\Memo;
use App\Models\OrganizationCenter\Tasks\Task\Task;
use App\Models\User;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Lawsuit extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, Cachable;

    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */
    protected $fillable = [
        'name',
        'lawsuit_number',
        'project_id',
        'entitie_id',
        'main_courts_id',
        'regions_id',
        'circle',
        'lawsuit_attachment',
        'our_proof',
        'opponent_proof',

        // التصنيفات
        'category_id',
        'subcategory_id',
        'lawsuit_type_id',

        'lawsuit_subject', // موضوع الدعوى
        'plaintiff_requests', // طلبات المدعي
        'lawsuit_proofs', // أسانيد الدعوى

        // مذكرة الدفاع الأولى
        'defense_memo',
        'defense_memo_attachment',

        // الأحكام
        'judgment',
        'judgment_attachment',

        // الطلبات
        'request',
        'request_attachment',

        // القرارات
        'decision',
        'decision_attachment',
        'lawsuit_status',

        'created_by',
        'updated_by',
    ];


    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created' => 'تم إنشاء الدعوى',
            'updated' => 'تم تحديث الدعوى',
            'deleted' => 'تم حذف الدعوى',
            'restored' => 'تم استعادة الدعوى',
            'forceDeleted' => 'تم حذف الدعوى بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('lawsuit')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} الدعوى";
            });
    }


    protected $casts = [
        'lawsuit_status'    => LawsuitStatus::class,

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
    // public function customer()
    // {
    //     return $this->belongsTo(Customers::class);
    // }

    public function attorney()
    {
        return $this->belongsTo(Employees::class);
    }

    public function opponent()
    {
        return $this->belongsTo(Opponent::class);
    }


    public function  lawsuit_type() // type lawsuite
    {
        return $this->belongsTo(SettingsLawsuitsType::class, 'lawsuit_type_id');
    }

    public function  department_contract_cases()
    {
        return $this->belongsTo(SettingsDepartmentContractCase::class, 'department_contract_cases_id');
    }


    public function  project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }


    public function  entitie()
    {
        return $this->belongsTo(SettingsEntity::class, 'entitie_id');
    }


    public function  main_court()
    {
        return $this->belongsTo(SettingsMainCourt::class, 'main_courts_id');
    }


    public function  category()
    {
        return $this->belongsTo(SettingsCategories::class, 'category_id');
    }


    public function  region()
    {
        return $this->belongsTo(SettingsRegion::class, 'regions_id');
    }


    public function sessions()
    {
        return $this->hasMany(Session::class, 'lawsuit_id');
    }


    public function powerOfAttorneys()
    {
        return $this->belongsToMany(PowerOfAttorney::class, 'lawsuit_power_of_attorneys', 'lawsuit_id', 'power_of_attorney_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    //

    public function plaintiffsCustomers()
    {
        return $this->morphedByMany(
            Customers::class,
            'plaintiff',
            'lawsuit_plaintiffs',
            'lawsuit_id',
            'plaintiff_id'
        )->withPivot('plaintiff_type');
    }

    /**
     * علاقة المدعين من جدول الخصوم (Opponents).
     */
    public function plaintiffsOpponents()
    {
        return $this->morphedByMany(
            Opponent::class,
            'plaintiff',
            'lawsuit_plaintiffs',
            'lawsuit_id',
            'plaintiff_id'
        )->withPivot('plaintiff_type');
    }

    /**
     * دالة مساعدة لجمع جميع المدعين من كلا الجدولين.
     */
    public function getAllPlaintiffsAttribute()
    {
        // جلب المدعين من جدول العملاء وإضافة نوعهم
        $customers = $this->plaintiffsCustomers->map(function ($customer) {
            $customer->plaintiff_type = 'App\Models\OperationsCenter\Customer';
            return $customer;
        });

        // جلب المدعين من جدول الخصوم وإضافة نوعهم
        $opponents = $this->plaintiffsOpponents->map(function ($opponent) {
            $opponent->plaintiff_type = 'App\Models\LegalAffair\Opponent';
            return $opponent;
        });

        // دمج المجموعتين
        return $customers->concat($opponents);
    }


    /**
     * علاقة المدعى عليهم من جدول العملاء (Customers).
     */
    public function defendantsCustomers()
    {
        return $this->morphedByMany(
            Customers::class,
            'defendant',
            'lawsuit_defendants',
            'lawsuit_id',
            'defendant_id'
        )->withPivot('defendant_type');
    }

    /**
     * علاقة المدعى عليهم من جدول الخصوم (Opponents).
     */
    public function defendantsOpponents()
    {
        return $this->morphedByMany(
            Opponent::class,
            'defendant',
            'lawsuit_defendants',
            'lawsuit_id',
            'defendant_id'
        )->withPivot('defendant_type');
    }

    /**
     * دالة مساعدة لجمع جميع المدعى عليهم من كلا الجدولين.
     */
    public function getAllDefendantsAttribute()
    {
        $customers = $this->defendantsCustomers->map(function ($customer) {
            $customer->defendant_type = 'App\Models\OperationsCenter\Customer';
            return $customer;
        });

        $opponents = $this->defendantsOpponents->map(function ($opponent) {
            $opponent->defendant_type = 'App\Models\LegalAffair\Opponent';
            return $opponent;
        });

        // دمج المجموعتين
        return $customers->concat($opponents);
    }

    // تعريف العلاقة بين الدعاوي والمرفقات
    public function attachments()
    {
        return $this->hasMany(LawsuitAttachment::class);
    }


    public function assignedEmployees()
    {
        return $this->belongsToMany(Employees::class, 'assigned_lawsuits', 'lawsuit_id', 'assigned_to', 'id', 'user_id')
            ->withPivot('user_accepted_id', 'status')
            ->using(AssignedLawsuits::class)
            ->withTimestamps();
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'lawsuits_id');
    }


    public function getAssignedEmployeesWithUserAcceptedAttribute()
    {
        return $this->assignedEmployees->map(function ($employee) {
            $employee->userAcceptedData = $employee->pivot->userAccepted;
            return $employee;
        });
    }


    //الملاظات
    public function notes()
    {
        return $this->hasMany(LawsuitNote::class)->orderBy('created_at', 'desc');
    }


    public function getHijriCreatedAttribute()
    {
        return Hijri::ShortDate($this->created_at);
    }

    // المذكرات
    public function memos()
    {
        return $this->hasMany(Memo::class);
    }


    public function getPrimaryContractCustomerIdAttribute()
    {
        return $this->project->primaryContract()->first()->customer_id;
    }


    // الدالة الخاصة بتبديل حالة الدعوى ال  active
    /**
     * تحديث حالة الدعوى بناءً على حالة المكلفين.
     */
    public function updateStatusBasedOnAssignedEmployees()
    {
        $totalAssigned = $this->assignedEmployees()->count();
        $accepted = $this->assignedEmployees()->wherePivot('status', 'accepted')->count();
        $rejected = $this->assignedEmployees()->wherePivot('status', 'rejected')->count();

        if ($accepted > 0 && $rejected < $totalAssigned) {
            $this->lawsuit_status = 'active';
        } elseif ($rejected === $totalAssigned && $accepted === 0) {
            $this->lawsuit_status = 'rejected';
        } else {
            $this->lawsuit_status = 'pending';
        }

        $this->save();
    }
}
