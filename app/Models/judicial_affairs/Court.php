<?php

namespace App\Models\judicial_affairs;

use App\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Court extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, BelongsToTenant;
    protected $dates = ['deleted_at']; 
    protected $fillable = [
        'name', 'address', 'city', 'state', 'postal_code', 'country',
        'court_type_id', 'phone', 'fax', 'email', 'website', 'notes',
        'created_by', 'updated_by', 'deleted_by'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created' => 'تم إنشاء المحكمة',
            'updated' => 'تم تحديث المحكمة',
            'deleted' => 'تم حذف المحكمة',
            'restored' => 'تم استعادة المحكمة',
            'forceDeleted' => 'تم حذف المحكمة بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('court')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} المحكمة";
            });
    }


    public function lawsuits()
    {
        return $this->hasMany(Lawsuit::class);
    }

    public function judges()
    {
        return $this->hasMany(Judge::class);
    }

    public function courtrooms()
    {
        return $this->hasMany(Courtroom::class);
    }
}
