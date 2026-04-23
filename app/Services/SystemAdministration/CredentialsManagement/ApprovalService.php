<?php

namespace App\Services\SystemAdministration\CredentialsManagement;

use App\Models\SystemAdministration\CredentialsManagement\{
    ApprovalFlow,
    ApprovalLevel
};
use DomainException;

class ApprovalService
{
    /*
    |--------------------------------------------------------------------------
    | Toggle Approval Level Assignment
    |--------------------------------------------------------------------------
    | Handles the assignment/unassignment of employees to approval levels
    | with proper validation and level sequencing.
    */
    public function toggleApprovalLevel(int $flowId, int $level, bool $assign, ?int $employeeId = null): ApprovalFlow
    {
        $flow = $this->getFlowWithLevels($flowId);
        $currentLevel = $this->getCurrentLevel($flow, $level);

        if ($assign) {
            $this->validateAssignment($flow, $level, $employeeId);
            $this->assignEmployee($currentLevel, $employeeId);
        } else {
            $this->validateUnassignment($flow, $level);
            $this->unassignEmployee($currentLevel);
        }

        return $flow->fresh(['levels' => function ($query) {
            $query->with('employee:id,name,nickname')->orderBy('level');
        }]);
    }

    /*
    |--------------------------------------------------------------------------
    | Add New Approval Level
    |--------------------------------------------------------------------------
    | Creates a new approval level for the specified flow.
    */
    public function addApprovalLevel(ApprovalFlow $flow): ApprovalFlow
    {
        \Log::info('Starting addApprovalLevel', [
            'flow_id' => $flow->id,
            'existing_levels' => $flow->levels->pluck('level')->toArray()
        ]);

        try {
            $nextLevel = $this->getNextLevelNumber($flow);
            \Log::info('Next level calculated', ['next_level' => $nextLevel]);

            $createdLevel = $flow->levels()->create([
                'level' => $nextLevel,
                'employee_id' => null
            ]);
            \Log::info('Level creation attempted', [
                'created' => (bool)$createdLevel,
                'created_level_id' => $createdLevel?->id,
                'created_level' => $createdLevel?->level
            ]);

            $freshFlow = $flow->fresh(['levels' => function ($query) {
                $query->with('employee:id,name,nickname')->orderBy('level');
            }]);

            \Log::info('addApprovalLevel completed', [
                'flow_id' => $flow->id,
                'levels_after' => $freshFlow->levels->pluck('level')->toArray()
            ]);

            return $freshFlow;
        } catch (\Throwable $e) {
            \Log::error('Error in addApprovalLevel', [
                'flow_id' => $flow->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get Flow With Levels
    |--------------------------------------------------------------------------
    | Retrieves the approval flow with its levels and employees.
    */
    private function getFlowWithLevels(int $flowId): ApprovalFlow
    {
        return ApprovalFlow::with(['levels' => function ($query) {
            $query->with('employee:id,name,nickname')->orderBy('level');
        }])->findOrFail($flowId);
    }


    /*
    |--------------------------------------------------------------------------
    | Get Current Level
    |--------------------------------------------------------------------------
    | Finds the specific approval level or throws exception if not found.
    */
    private function getCurrentLevel(ApprovalFlow $flow, int $level): ApprovalLevel
    {
        $currentLevel = $flow->levels->firstWhere('level', $level);

        if (!$currentLevel) {
            throw new DomainException('المستوى المحدد غير موجود.');
        }

        return $currentLevel;
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Assignment
    |--------------------------------------------------------------------------
    | Validates that assignment can be performed according to business rules.
    */
    private function validateAssignment(ApprovalFlow $flow, int $level, ?int $employeeId): void
    {
        if (!$employeeId) {
            throw new DomainException('يجب اختيار موظف للاعتماد.');
        }

        // Check if previous levels are assigned
        $previousUnassignedLevel = $flow->levels
            ->where('level', '<', $level)
            ->first(fn($l) => !$l->isAssigned());

        if ($previousUnassignedLevel) {
            throw new DomainException('يجب تعيين المستويات السابقة أولاً.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Unassignment
    |--------------------------------------------------------------------------
    | Validates that unassignment can be performed according to business rules.
    */
    private function validateUnassignment(ApprovalFlow $flow, int $level): void
    {
        // Check if higher levels are assigned
        $higherAssignedLevel = $flow->levels
            ->where('level', '>', $level)
            ->first(fn($l) => $l->isAssigned());

        if ($higherAssignedLevel) {
            throw new DomainException('يجب إلغاء تعيين المستويات الأعلى أولاً.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Remove level
    |--------------------------------------------------------------------------
    | Remove an approval level (only if no employee is assigned) .
    */
    public function removeApprovalLevel(int $flowId, int $level): ApprovalFlow
    {
        $flow = $this->getFlowWithLevels($flowId);
        $currentLevel = $this->getCurrentLevel($flow, $level);

        if ($currentLevel->isAssigned()) {
            throw new DomainException('لا يمكنك حذف هذا المستوى قبل إلغاء تعيين الموظف.');
        }

        // Delete the level
        $currentLevel->delete();

        // Shift down subsequent levels
        $flow->levels()
            ->where('level', '>', $level)
            ->decrement('level');

        return $flow->fresh(['levels' => function ($query) {
            $query->with('employee:id,name,nickname')->orderBy('level');
        }]);
    }


    /*
    |--------------------------------------------------------------------------
    | Assign Employee
    |--------------------------------------------------------------------------
    | Assigns an employee to the approval level.
    */
    private function assignEmployee(ApprovalLevel $level, int $employeeId): void
    {
        $level->update(['employee_id' => $employeeId]);
    }

    /*
    |--------------------------------------------------------------------------
    | Unassign Employee
    |--------------------------------------------------------------------------
    | Removes employee assignment from the approval level.
    */
    private function unassignEmployee(ApprovalLevel $level): void
    {
        $level->update(['employee_id' => null]);
    }

    /*
    |--------------------------------------------------------------------------
    | Get Next Level Number
    |--------------------------------------------------------------------------
    | Calculates the next level number for new approval levels.
    */
    private function getNextLevelNumber(ApprovalFlow $flow): int
    {
        return $flow->levels()->max('level') + 1;
    }
}
