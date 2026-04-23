<?php

namespace App\Models\general_setting;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SettingsViolationCategory extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'name',
        'user_id',
        'status'
    ];


    /**
     * العلاقة مع المخالفات التابعة لهذا التصنيف.
     */
    public function settingsViolations()
    {
        return $this->hasMany(SettingsViolation::class, 'settings_violation_category_id');
    }

    /**
     * العلاقة مع المستخدم الذي أنشأ التصنيف.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getHijriCreatedAtAttribute()
    {
        return Hijri::ShortDate($this->created_at);
    }
}
