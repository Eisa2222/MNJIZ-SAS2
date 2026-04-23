<?php

namespace App\Models\LegalAffair\Opponent;


use Alkoumi\LaravelHijriDate\Hijri;
use App\Enums\LegalAffair\Opponent\OpponentType;
use App\Models\general_setting\SettingsRegion;
use App\Models\LegalAffair\Lawsuit\Lawsuit;
use App\Models\User;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Opponent extends Model
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
        'email',
        'contact_number',
        'bio',
        'settings_region_id',
        'type',
        'commercial_registration',
        'unified_number',
        'identity_number',

        'created_by',
        'updated_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created'       => 'تم إنشاء الخصم',
            'updated'       => 'تم تحديث الخصم',
            'deleted'       => 'تم حذف الخصم',
            'restored'      => 'تم استعادة الخصم',
            'forceDeleted'  => 'تم حذف الخصم بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('opponent')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} الخصم";
            });
    }


    protected $casts = [
        'type'          => OpponentType::class,

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
    public function region()
    {
        return $this->belongsTo(SettingsRegion::class, 'settings_region_id');
    }

    // مدعي و مدعى عليه
    public function lawsuitsAsPlaintiff()
    {
        return $this->belongsToMany(
            Lawsuit::class,
            'lawsuit_plaintiffs',
            'plaintiff_id',
            'lawsuit_id'
        )->where('lawsuit_plaintiffs.plaintiff_type', 'App\\Models\\LegalAffair\\Opponent');
    }

    public function lawsuitsAsDefendant()
    {
        return $this->belongsToMany(
            Lawsuit::class,
            'lawsuit_defendants',
            'defendant_id',
            'lawsuit_id'
        )->where('lawsuit_defendants.defendant_type', 'App\\Models\\LegalAffair\\Opponent');
    }


    public function authorizations()
    {
        return $this->hasMany(OpponentAuthorization::class, 'opponent_id');
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
    |
    |============================================================================
    |============================================================================
    */
    public function getHijriCreatedAtAttribute()
    {
        return Hijri::ShortDate($this->created_at);
    }
}
