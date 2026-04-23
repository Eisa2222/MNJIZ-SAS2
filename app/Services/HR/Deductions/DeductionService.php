<?php

namespace App\Services\HR\Deductions;

use App\Contracts\ErrorHandlerInterface;
use App\Data\Hr\Deductions\DeductionData;
use App\Enums\Hr\Deduction\DeductionStatus;
use App\Models\Hr\Deductions\Deduction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeductionService
{
    public function __construct(
        private ErrorHandlerInterface $errorHandler
    ) {}

    // create
    public function createDeduction(DeductionData $dto): void
    {
        $this->errorHandler->execute(function () use ($dto) {
            return DB::transaction(function () use ($dto) {
                $serial = $this->generateDeductionNumber();

                $deduction = new Deduction([
                    'deduction_number'   => $serial,
                    'employee_id'        => $dto->employee_id,
                    'deduction_type'     => $dto->deduction_type->value ?? $dto->deduction_type,
                    'amount'             => $dto->amount,
                    'deduction_date'     => $dto->deduction_date->format('Y-m-d'),
                    'status'             => DeductionStatus::Pending->value,
                    'notes'              => $dto->notes,
                    'created_by'         => $this->currentEmployeeId(),
                ]);

                $deduction->save();
            });
        }, 'حدث خطأ أثناء حفظ الخصم المالي');
    }


    // update
    public function updateDeduction(int $id, DeductionData $dto): void
    {
        $this->errorHandler->execute(function () use ($id, $dto) {
            return DB::transaction(function () use ($id, $dto) {
                $deduction = Deduction::findOrFail($id);

                // فحص إمكانية التعديل
                if (!$deduction->status->canEdit()) {
                    throw ValidationException::withMessages([
                        'status' => ' لا يمكن تعديل الخصم المالي في حالة ' . $deduction->status->label()
                    ]);
                }

                $deduction->update([
                    'employee_id'    => $dto->employee_id,
                    'deduction_type' => $dto->deduction_type->value ?? $dto->deduction_type,
                    'amount'         => $dto->amount,
                    'deduction_date' => $dto->deduction_date->format('Y-m-d'),
                    'notes'          => $dto->notes,
                    'updated_by'     => $this->currentEmployeeId(),
                ]);
            });
        }, 'حدث خطأ أثناء تحديث الخصم المالي');
    }


    // delete
    public function deleteDeduction(int $id): bool
    {
        return $this->errorHandler->execute(function () use ($id) {
            return DB::transaction(function () use ($id) {
                $deduction = Deduction::findOrFail($id);

                if (!$deduction->status->canDelete()) {
                    throw ValidationException::withMessages([
                        'status' => " لا يمكن حذف الخصم المالي في حالة " . $deduction->status->label()
                    ]);
                }

                return $deduction->delete();
            });
        }, 'حدث خطأ أثناء حذف الخصم المالي');
    }



    /*
    |============================================================================
    |============================================================================
    |                        Pravate Methods
    |============================================================================
    |============================================================================
    */
    private function generateDeductionNumber(): string
    {
        $last = Deduction::withTrashed()
            ->select('deduction_number')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        $next = $last && str_starts_with($last->deduction_number, 'DED-')
            ? ((int) str_replace('DED-', '', $last->deduction_number) + 1) : 1;

        return 'DED-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }


    private function currentEmployeeId(): int
    {
        $user = Auth::user();
        if (!$user || !$user->employee) {
            throw new \Exception('المستخدم غير مرتبط بموظف في النظام.');
        }
        return $user->employee->id;
    }
}
