<?php

namespace App\Models\general_setting;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SettingsSubcategories extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;
    protected $dates = ['deleted_at'];
    protected $fillable = ['category_id', 'name', 'user_id', 'status','position'];

    /**
     * إعداد خيارات تسجيل النشاط.
     */
    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created' => 'تم إنشاء التصنيف الفرعي',
            'updated' => 'تم تحديث التصنيف الفرعي',
            'deleted' => 'تم حذف التصنيف الفرعي',
            'restored' => 'تم استعادة التصنيف الفرعي',
            'forceDeleted' => 'تم حذف التصنيف الفرعي بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('settings_subcategories')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} التصنيف الفرعي";
            });
    }

    /**
     * علاقة Many to One مع التصنيفات الرئيسية.
     */
    public function category()
    {
        return $this->belongsTo(SettingsCategories::class, 'category_id');
    }

    /**
     * علاقة One to Many مع أنواع القضايا المرتبطة بالتصنيف الفرعي.
     */
    public function lawsuits_types()
    {
        return $this->hasMany(SettingsLawsuitsType::class, 'subcategory_id');
    }

    /**
     * علاقة Many to One مع المستخدم.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($subcategory) {
            // حذف أنواع الدعاوى المرتبطة
            $subcategory->lawsuits_types()->delete();
        });

        // إذا كنت تستخدم SoftDeletes وترغب في استعادة الأنواع المرتبطة عند استعادة التصنيف الفرعي
        static::restoring(function ($subcategory) {
            $subcategory->lawsuits_types()->withTrashed()->restore();
        });
    }

    public function getHijriCreatedAtAttribute()
    {
        return Hijri::ShortDate($this->created_at);
    }
}
