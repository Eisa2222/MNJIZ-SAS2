<?php

namespace App\Models;


use App\Models\chat\Message;
use App\Models\general_setting\SettingsCountry;
use App\Models\Hr\Employees\Employees;
use App\Models\judicial_affairs\SessionCommentMention;
use App\Models\LegalAI\AiChat;
use App\Models\Task\Task;
use App\Models\Task\TaskStep;
use App\Services\EmailService;
use App\Tenancy\Concerns\BelongsToTenant;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, Cachable, BelongsToTenant;

    use HasRoles {
        hasPermissionTo as protected traitHasPermissionTo;
    }



    /*
    |--------------------------------------------------------------------------
    | date
    |--------------------------------------------------------------------------
    */
    protected $dates = ['deleted_at'];



    /*
    |--------------------------------------------------------------------------
    | filable
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'status',
        // 'role',
        'nationality',
        'job',
        'image',
        'microsoft_id',
        'microsoft_token',
        'microsoft_refresh_token',
        'microsoft_token_expires',
        'must_change_password',
        'tour_completed',
        'tour_task_completed',
    ];



    /*
    |--------------------------------------------------------------------------
    | hidden
    |--------------------------------------------------------------------------
    */
    protected $hidden = [
        'password',
        'remember_token',
    ];



    /*
    |--------------------------------------------------------------------------
    | casts
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'microsoft_token_expires' => 'datetime',
        'microsoft_token' => 'encrypted',
        'microsoft_refresh_token' => 'encrypted',
    ];


    /*
    |--------------------------------------------------------------------------
    | countery relation
    |--------------------------------------------------------------------------
    */
    public function country()
    {
        return $this->belongsTo(SettingsCountry::class, 'nationality');
    }


    /*
    |--------------------------------------------------------------------------
    | employee relation
    |--------------------------------------------------------------------------
    */
    public function employee()
    {
        return $this->hasOne(Employees::class, 'user_id'); // user_id هو المفتاح الأجنبي في جدول الموظفين
    }



    /*
    |--------------------------------------------------------------------------
    | lawsuit note relation
    |--------------------------------------------------------------------------
    */
    public function lawsuitNotes()
    {
        return $this->hasMany(LawsuitNote::class);
    }


    /*
    |--------------------------------------------------------------------------
    | lawsuit not replies relation
    |--------------------------------------------------------------------------
    */
    public function lawsuitNoteReplies()
    {
        return $this->hasMany(LawsuitNoteReply::class);
    }



    /*
    |--------------------------------------------------------------------------
    | messages relation
    |--------------------------------------------------------------------------
    */
    public function messagesSent()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }



    /*
    |--------------------------------------------------------------------------
    | Message relation
    |--------------------------------------------------------------------------
    */
    public function messagesReceived()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }


    /*
    |--------------------------------------------------------------------------
    | Email relation
    |--------------------------------------------------------------------------
    */
    public function sendPasswordResetNotification($token)
    {
        $emailService = app(EmailService::class);

        // تمرير كائن المستخدم والتوكن إلى خدمة البريد الإلكتروني
        $emailService->sendPasswordResetNotification($this, $token);
    }


    /*
    |--------------------------------------------------------------------------
    | Session comment mention rlation
    |--------------------------------------------------------------------------
    */
    public function mentionsMade()
    {
        return $this->hasMany(SessionCommentMention::class, 'mentioner_user_id');
    }



    /*
    |--------------------------------------------------------------------------
    | Session comment mention rlation
    |--------------------------------------------------------------------------
    */
    public function mentionsReceived()
    {
        return $this->hasMany(SessionCommentMention::class, 'mentioned_user_id');
    }



    /*
    |--------------------------------------------------------------------------
    | Permission relation
    |--------------------------------------------------------------------------
    */
    public function deniedPermissions()
    {
        return $this->belongsToMany(Permission::class, 'user_denied_permissions');
    }


    /*
    |--------------------------------------------------------------------------
    | Permission relation
    |--------------------------------------------------------------------------
    */
    public function additionalPermissions()
    {
        return $this->belongsToMany(Permission::class, 'user_additional_permissions');
    }


    /*
    |--------------------------------------------------------------------------
    | if has Permission
    |--------------------------------------------------------------------------
    */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        $permissionName = is_string($permission) ? $permission : $permission->name;

        // تحميل العلاقات إذا لم تكن محملة
        $this->loadMissing(['deniedPermissions', 'additionalPermissions']);

        // إذا كانت الصلاحية منزوعة من المستخدم
        if ($this->deniedPermissions->contains('name', $permissionName)) {
            return false;
        }

        // إذا كانت الصلاحية مضافة بشكل فردي للمستخدم
        if ($this->additionalPermissions->contains('name', $permissionName)) {
            return true;
        }

        // استخدام الصلاحيات من الأدوار
        return $this->traitHasPermissionTo($permission, $guardName);
    }


    /*
    |--------------------------------------------------------------------------
    | Finger Print Relation
    |--------------------------------------------------------------------------
    */
    public function fingerprints()
    {
        return $this->hasOne(Fingerprint::class);
    }



    /*
    |--------------------------------------------------------------------------
    | Attendance relation
    |--------------------------------------------------------------------------
    */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }



    /*
    |--------------------------------------------------------------------------
    | task relation
    |--------------------------------------------------------------------------
    */
    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_user')
            ->withPivot('assigned_at', 'user_attachment', 'graph_list_id', 'graph_task_id', 'graph_event_id')
            ->withTimestamps();
    }



    /*
    |--------------------------------------------------------------------------
    | step task relation
    |--------------------------------------------------------------------------
    */
    public function taskSteps()
    {
        return $this->belongsToMany(TaskStep::class, 'task_step_user', 'user_id', 'task_step_id')
            ->withPivot('assigned_at', 'user_attachment', 'graph_list_id', 'graph_task_id', 'graph_event_id')
            ->withTimestamps();
    }


    /*
    |--------------------------------------------------------------------------
    | تعريف علاقة "واحد إلى متعدد" مع محادثات الذكاء الاصطناعي.
    |--------------------------------------------------------------------------
    | المستخدم الواحد يمكن أن يكون لديه العديد من محادثات الـ AI.
    */
    public function aiChats(): HasMany
    {
        return $this->hasMany(AiChat::class, 'user_id');
    }
}
