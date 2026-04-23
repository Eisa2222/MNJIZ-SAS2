<?php

namespace App\Models\general_setting;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SettingsCategories extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;
    protected $fillable = ['name', 'user_id', 'status', 'position'];

    protected $dates = ['deleted_at'];

    protected static function boot()
    {
        parent::boot();

        // عند حذف التصنيف الرئيسي، نقوم بحذف (soft delete) التصنيفات الفرعية التابعة له
        static::deleting(function ($category) {
            foreach ($category->subcategories as $subcategory) {
                $subcategory->delete();
            }
        });

        // عند استعادة التصنيف الرئيسي، نقوم باستعادة التصنيفات الفرعية التابعة له أيضًا
        static::restoring(function ($category) {
            foreach ($category->subcategories()->withTrashed()->get() as $subcategory) {
                $subcategory->restore();
            }
        });
    }



    public function getActivitylogOptions(): LogOptions
    {
        $descriptions = [
            'created' => 'تم إنشاء الإعداد',
            'updated' => 'تم تحديث الإعداد',
            'deleted' => 'تم حذف الإعداد',
            'restored' => 'تم استعادة الإعداد',
            'forceDeleted' => 'تم حذف الإعداد بشكل نهائي',
        ];

        return LogOptions::defaults()
            ->logAll()
            ->useLogName('settings')
            ->setDescriptionForEvent(function (string $eventName) use ($descriptions) {
                return $descriptions[$eventName] ?? "تم {$eventName} الإعداد";
            });
    }



    // علاقة One to Many مع التصنيفات الفرعية
    public function subcategories()
    {
        return $this->hasMany(SettingsSubcategories::class, 'category_id');
    }

    // علاقة Many to One مع المستخدم
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getHijriCreatedAtAttribute()
    {
        return Hijri::ShortDate($this->created_at);
    }
}
