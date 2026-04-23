<?php

namespace App\Models\SystemAdministration\CredentialsManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class ApprovalFlow extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Model Configuration
    |--------------------------------------------------------------------------
    | Basic model configuration including table and fillable fields.
    */
    protected $table = 'approval_flows';

    protected $fillable = [
        'type',
        'name',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Approval Levels Relationship
    |--------------------------------------------------------------------------
    | Defines the relationship with approval levels ordered by level number.
    */
    public function levels(): HasMany
    {
        return $this->hasMany(ApprovalLevel::class, 'approval_flow_id')
            ->withoutTrashed()
            ->orderBy('level');
    }

    /*
    |--------------------------------------------------------------------------
    | Active Levels Relationship
    |--------------------------------------------------------------------------
    | Retrieves only levels that have assigned employees.
    */
    public function activeLevels(): HasMany
    {
        return $this->levels()->whereNotNull('employee_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scope: Active Flows
    |--------------------------------------------------------------------------
    | Filters only active approval flows.
    */
    public function scopeActive(Builder $query): Builder
    {
        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | Scope: By Type
    |--------------------------------------------------------------------------
    | Filters flows by specific type.
    */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /*
    |--------------------------------------------------------------------------
    | Get Maximum Level
    |--------------------------------------------------------------------------
    | Returns the highest level number in this flow.
    */
    public function getMaxLevelAttribute(): int
    {
        return $this->levels()->max('level') ?? 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Get Assigned Levels Count
    |--------------------------------------------------------------------------
    | Returns count of levels with assigned employees.
    */
    public function getAssignedLevelsCountAttribute(): int
    {
        return $this->levels()->whereNotNull('employee_id')->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Get Total Levels Count
    |--------------------------------------------------------------------------
    | Returns total count of levels in this flow.
    */
    public function getTotalLevelsCountAttribute(): int
    {
        return $this->levels()->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Check if Flow is Complete
    |--------------------------------------------------------------------------
    | Determines if all levels have assigned employees.
    */
    public function isComplete(): bool
    {
        return $this->total_levels_count > 0 &&
            $this->assigned_levels_count === $this->total_levels_count;
    }

    /*
    |--------------------------------------------------------------------------
    | Get Next Available Level
    |--------------------------------------------------------------------------
    | Returns the next level number that can be created.
    */
    public function getNextAvailableLevel(): int
    {
        return $this->max_level + 1;
    }

    /*
    |--------------------------------------------------------------------------
    | Get Flow Type Label
    |--------------------------------------------------------------------------
    | Returns localized label for the flow type.
    */
    public static function getAllTypeLabels(): array
    {
        return [
            'offer' => 'اعتمادات العروض',
            'contract' => 'اعتمادات العقود',
            'leave' => 'اعتمادات الإجازات',
            'wps' => 'اعتمادات مسيرات الرواتب',
            'clearance_certificate' => 'اعتمادات إخلاء الطرف',
            'advance' => 'اعتمادات السلفيات',
            'reward' => 'اعتمادات المكافآت',
            'deduction' => 'اعتمادات الخصومات',
            'content' => 'اعتمادات المحتوى',
            'custody' => 'اعتمادات العهد'
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Get Flow Type Label - تحديث الدالة الموجودة
    |--------------------------------------------------------------------------
    | Returns localized label for the flow type.
    */
    public function getTypeLabel(): string
    {
        return self::getAllTypeLabels()[$this->type] ?? $this->type;
    }

    /*
    |--------------------------------------------------------------------------
    | Get Completion Percentage
    |--------------------------------------------------------------------------
    | Returns the percentage of completed level assignments.
    */
    public function getCompletionPercentage(): float
    {
        if ($this->total_levels_count === 0) {
            return 0;
        }

        return round(($this->assigned_levels_count / $this->total_levels_count) * 100, 2);
    }
}
