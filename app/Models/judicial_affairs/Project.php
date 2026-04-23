<?php

namespace App\Models\judicial_affairs;

use App\Models\OperationsCenter\Customer\Customers;
use App\Models\general_setting\SettingsContractStatus;
use App\Models\LegalAffair\Lawsuit\Lawsuit;
use App\Models\LegalAffair\PowerOfAttorney\PowerOfAttorney;
use App\Models\OperationsCenter\Contract\Contract;
use App\Models\OperationsCenter\ExceptionalContract\ExceptionalContract;
use App\Models\OrganizationCenter\Tasks\Task\Task;
use App\Models\User;
use App\Traits\HijriDateConversion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Project extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, HijriDateConversion;
    /*
    |============================================================================
    |============================================================================
    |                               Properties
    |============================================================================
    |============================================================================
    */
    protected $fillable = [
        'project_name',
        'project_number',
        'customer_id',
        'manager_user_id',
        'start_date',
        'end_date',
        'description',
        'opponent_id',
        'financial_claim',
        'non_financial_claim',
        'other_claim',
        'contractual_closure',
        'opponent_proof_number',
        'created_by',
        'complate_user_id',
        'project_type',
        'scope_of_work',
        'contract_type',
        'technical_manager_id',
        'new_status_id',
        'exceptional_contract_id', // العقد الاستثنائي
    ];

    protected $dates = ['deleted_at'];

    protected $hijriDateFields = [
        'start_date',
        'end_date',
        'contractual_closure',
    ];

    public const TYPE_CONTRACT_MAIN         = 'main_contract';
    public const TYPE_CONTRACT_EXCEPTIONAL  = 'exceptional_contract';

    protected static array $typeContractOptions = [
        self::TYPE_CONTRACT_MAIN  => [
            'name' => self::TYPE_CONTRACT_MAIN,
            'text' => 'عقد رئيسي',
        ],
        self::TYPE_CONTRACT_EXCEPTIONAL => [
            'name' => self::TYPE_CONTRACT_EXCEPTIONAL,
            'text' => 'عقد استثنائي',
        ],
    ];


    /*
    |============================================================================
    |============================================================================
    |                               Relations
    |============================================================================
    |============================================================================
    */
    public function exceptional_contract()
    {
        return $this->belongsTo(ExceptionalContract::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customers::class);
    }

    public function manager_user()
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    public function technical_manager()
    {
        return $this->belongsTo(User::class, 'technical_manager_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'project_id');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function attachments()
    {
        return $this->hasMany(ProjectAttachment::class);
    }

    public function power_of_attorney()
    {
        return $this->belongsTo(PowerOfAttorney::class, 'power_of_attorney_id');
    }

    public function primaryContract()
    {
        return $this->belongsToMany(Contract::class, 'contract_project')
            ->withPivot('contract_type')
            ->wherePivot('contract_type', 'primary')
            ->limit(1); // تحديد عقد واحد فقط
    }


    public function contracts()
    {
        return $this->belongsToMany(Contract::class, 'contract_project')
            ->withPivot('contract_type') // للوصول إلى نوع العقد (primary أو secondary)
            ->withTimestamps();          // لو كنت تستخدم أعمدة created_at وupdated_at في الجدول الوسيط
    }

    public function lawsuits()
    {
        return $this->hasMany(Lawsuit::class, 'project_id');
    }

    public function project_status()
    {
        return $this->belongsTo(SettingsContractStatus::class, 'new_status_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created' => 'تم إنشاء المشروع',
            'updated' => 'تم تحديث المشروع',
            'deleted' => 'تم حذف المشروع',
            'restored' => 'تم استعادة المشروع',
            'forceDeleted' => 'تم حذف المشروع بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('project')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} المشروع";
            });
    }

    public function complate_user()
    {
        return $this->belongsTo(User::class, 'complate_user_id');
    }

    public function start_user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // إضافة العلاقة الجديدة لفريق المشروع
    public function teamMembers()
    {
        return $this->belongsToMany(User::class, 'project_employee', 'project_id', 'employee_id')->withTimestamps();
    }


    /*
    |============================================================================
    |============================================================================
    |                               Accessors
    |============================================================================
    |============================================================================
    */

    public static function getTypeContractOptions(): array
    {
        return collect(self::$typeContractOptions)
            ->mapWithKeys(fn($item, $key) => [$key => $item['text']])
            ->all();
    }

    public function getStartDateAttribute($value)
    {
        return $this->convertToHijri($value);
    }


    public function getContractualClosureAttribute($value)
    {
        return $this->convertToHijri($value);
    }


    public function getEndDateAttribute($value)
    {
        return $this->convertToHijri($value);
    }


    public function getStartDateGregorianAttribute()
    {
        return $this->attributes['start_date'];
    }

    public function getContractualClosureGregorianAttribute()
    {
        return $this->attributes['contractual_closure'];
    }

    /*
    |--------------------------------------------------------------------------
    | نطاق لعرض المشاريع المرتبطة بالمستخدم الحالي
    |--------------------------------------------------------------------------
    | يشمل المشاريع التي أنشأها المستخدم أو هو مدير فني فيها أو مدير للمشروع أو عضو في فريق المشروع
    */
    public function scopeUserRelated($query)
    {
        $userId = auth()->id();

        return $query->where(function ($query) use ($userId) {
            // المشاريع التي أنشأها المستخدم
            $query->where('created_by', $userId)
                // أو المشاريع التي يكون المستخدم مدير شؤونها الفنية
                ->orWhere('technical_manager_id', $userId)
                // أو المشاريع التي يكون المستخدم مديرها
                ->orWhere('manager_user_id', $userId)
                // أو المشاريع التي يكون المستخدم عضوًا في فريقها
                ->orWhereHas('teamMembers', function ($subQuery) use ($userId) {
                    $subQuery->where('users.id', $userId);
                });
        });
    }
}
