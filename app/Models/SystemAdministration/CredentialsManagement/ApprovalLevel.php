<?php

namespace App\Models\SystemAdministration\CredentialsManagement;

use App\Models\Hr\Employees\Employees;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalLevel extends Model
{
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Model Configuration
    |--------------------------------------------------------------------------
    | Basic model configuration including table and fillable fields.
    */
    protected $table = 'approval_levels';

    protected $fillable = [
        'approval_flow_id',
        'level',
        'employee_id',
        'is_required',
        'description'
    ];

    protected $casts = [
        'level' => 'integer',
        'employee_id' => 'integer',
        'approval_flow_id' => 'integer',
        'is_required' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Approval Flow Relationship
    |--------------------------------------------------------------------------
    | Defines the relationship with the parent approval flow.
    */
    public function flow(): BelongsTo
    {
        return $this->belongsTo(ApprovalFlow::class, 'approval_flow_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Employee Relationship
    |--------------------------------------------------------------------------
    | Defines the relationship with the assigned employee.
    */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employees::class, 'employee_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scope: Assigned Levels
    |--------------------------------------------------------------------------
    | Filters only levels that have assigned employees.
    */
    public function scopeAssigned(Builder $query): Builder
    {
        return $query->whereNotNull('employee_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scope: Unassigned Levels
    |--------------------------------------------------------------------------
    | Filters only levels without assigned employees.
    */
    public function scopeUnassigned(Builder $query): Builder
    {
        return $query->whereNull('employee_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scope: Required Levels
    |--------------------------------------------------------------------------
    | Filters only required approval levels.
    */
    public function scopeRequired(Builder $query): Builder
    {
        return $query->where('is_required', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Scope: By Level Number
    |--------------------------------------------------------------------------
    | Filters by specific level number.
    */
    public function scopeByLevel(Builder $query, int $level): Builder
    {
        return $query->where('level', $level);
    }

    /*
    |--------------------------------------------------------------------------
    | Check if Level is Assigned
    |--------------------------------------------------------------------------
    | Determines if this level has an assigned employee.
    */
    public function isAssigned(): bool
    {
        return !is_null($this->employee_id);
    }

    /*
    |--------------------------------------------------------------------------
    | Check if Level is Required
    |--------------------------------------------------------------------------
    | Determines if this level is required for approval.
    */
    public function isRequired(): bool
    {
        return $this->is_required;
    }

    /*
    |--------------------------------------------------------------------------
    | Get Employee Name
    |--------------------------------------------------------------------------
    | Returns the assigned employee's name or default text.
    */
    public function getEmployeeName(): string
    {
        return $this->employee ? $this->employee->name : 'غير محدد';
    }

    /*
    |--------------------------------------------------------------------------
    | Get Employee Display Name
    |--------------------------------------------------------------------------
    | Returns employee name with nickname if available.
    */
    public function getEmployeeDisplayName(): string
    {
        return $this->employee ? $this->employee->name : 'غير محدد';
    }

    /*
    |--------------------------------------------------------------------------
    | Get Level Label
    |--------------------------------------------------------------------------
    | Returns formatted level label for display.
    */
    public function getLevelLabel(): string
    {
        return "المستوى {$this->level}";
    }

    /*
    |--------------------------------------------------------------------------
    | Check if Previous Level is Assigned
    |--------------------------------------------------------------------------
    | Validates if the previous level has an assigned employee.
    */
    public function isPreviousLevelAssigned(): bool
    {
        if ($this->level <= 1) {
            return true; // First level doesn't need previous validation
        }

        $previousLevel = $this->flow->levels()
            ->where('level', $this->level - 1)
            ->first();

        return $previousLevel && $previousLevel->isAssigned();
    }

    /*
    |--------------------------------------------------------------------------
    | Check if Next Level is Assigned
    |--------------------------------------------------------------------------
    | Validates if any higher level has an assigned employee.
    */
    public function hasAssignedNextLevel(): bool
    {
        return $this->flow->levels()
            ->where('level', '>', $this->level)
            ->whereNotNull('employee_id')
            ->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Can Assign Employee
    |--------------------------------------------------------------------------
    | Determines if an employee can be assigned to this level.
    */
    public function canAssignEmployee(): bool
    {
        return $this->isPreviousLevelAssigned();
    }

    /*
    |--------------------------------------------------------------------------
    | Can Unassign Employee
    |--------------------------------------------------------------------------
    | Determines if the employee can be unassigned from this level.
    */
    public function canUnassignEmployee(): bool
    {
        return !$this->hasAssignedNextLevel();
    }

    /*
    |--------------------------------------------------------------------------
    | Get Assignment Status
    |--------------------------------------------------------------------------
    | Returns the current assignment status with additional info.
    */
    public function getAssignmentStatus(): array
    {
        return [
            'is_assigned' => $this->isAssigned(),
            'can_assign' => $this->canAssignEmployee(),
            'can_unassign' => $this->canUnassignEmployee(),
            'employee_name' => $this->getEmployeeDisplayName(),
            'level_label' => $this->getLevelLabel(),
        ];
    }
}
