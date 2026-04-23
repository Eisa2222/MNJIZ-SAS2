<?php

namespace App\Services\HR\Advances;

use App\Contracts\ErrorHandlerInterface;
use App\Data\Hr\Advances\AdvanceData;
use App\Enums\Hr\Advance\AdvanceStatus;
use App\Models\Hr\Advances\Advance;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AdvanceService
{
    public function __construct(private ErrorHandlerInterface $errorHandler) {}


    // create
    public function createAdvance(AdvanceData $dto): void
    {
        //  هل الموظف يستحق سلفة
        $this->validateEmployeeEligibility($dto->employee_id);

        $this->errorHandler->execute(function () use ($dto) {
            return DB::transaction(function () use ($dto) {
                $serial = $this->generateAdvanceNumber();

                $advance = new Advance([
                    'advance_number'   => $serial,
                    'employee_id'      => $dto->employee_id,
                    'advance_type'     => $dto->advance_type->value ?? $dto->advance_type,
                    'amount'           => $dto->amount,
                    'advance_date'     => $dto->advance_date->format('Y-m-d'),
                    'due_date'         => $dto->due_date?->format('Y-m-d'),
                    'remaining_amount' => $dto->amount,
                    'status'           => AdvanceStatus::Pending->value,
                    'notes'            => $dto->notes,
                    'created_by'       => $this->currentEmployeeId(),
                ]);

                $advance->save();
            });
        }, 'حدث خطأ أثناء حفظ السلفة');
    }


    // update
    public function update(int $id, AdvanceData $dto): void
    {
        $this->errorHandler->execute(function () use ($id, $dto) {
            return DB::transaction(function () use ($id, $dto) {
                $advance = Advance::findOrFail($id);

                // فحص إمكانية التعديل
                if (!$advance->status->canEdit()) {
                    throw ValidationException::withMessages([
                        'status' => ' لا يمكن تعديل السلفة في حالة ' . $advance->status->label()
                    ]);
                }

                $advance->update([
                    'employee_id'   => $dto->employee_id,
                    'advance_type'  => $dto->advance_type->value ?? $dto->advance_type,
                    'amount'        => $dto->amount,
                    'advance_date'  => $dto->advance_date->format('Y-m-d'),
                    'due_date'      => $dto->due_date?->format('Y-m-d'),
                    'notes'         => $dto->notes,
                    'updated_by'    => $this->currentEmployeeId(),
                ]);

                // $this->recalculateRemainingAmount($advance);
            });
        }, 'حدث خطأ أثناء تحديث السلفة');
    }


    // delete
    public function delete(int $id): bool
    {
        return $this->errorHandler->execute(function () use ($id) {
            return DB::transaction(function () use ($id) {
                $advance = Advance::findOrFail($id);

                if (!$advance->status->canDelete()) {
                    throw ValidationException::withMessages([
                        'status' => " لا يمكن حذف السلفة في حالة " . $advance->status->label()
                    ]);
                }

                return $advance->delete();
            });
        }, 'حدث خطأ أثناء حذف السلفة');
    }


    // generateAdvanceNumber advance number
    public function generateAdvanceNumber(): string
    {
        $last = Advance::withTrashed()
            ->select('advance_number')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        $next = $last && str_starts_with($last->advance_number, 'ADV-')
            ? ((int) str_replace('ADV-', '', $last->advance_number) + 1) : 1;

        return 'ADV-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }


    // /**
    //  * تسجيل دفعة
    //  */
    // public function recordPayment(int $id, float $amount, ?string $notes = null): Advance
    // {
    //     return DB::transaction(function () use ($id, $amount, $notes) {
    //         $advance = Advance::findOrFail($id);

    //         if (!$advance->status->canMakePayment()) {
    //             throw ValidationException::withMessages([
    //                 'status' => 'لا يمكن تسجيل دفعة لهذه السلفة'
    //             ]);
    //         }

    //         if ($amount <= 0 || $amount > $advance->remaining_amount) {
    //             throw ValidationException::withMessages([
    //                 'amount' => 'مبلغ الدفعة غير صحيح'
    //             ]);
    //         }

    //         // تحديث المبلغ المتبقي
    //         $newRemainingAmount = $advance->remaining_amount - $amount;

    //         // تحديد الحالة الجديدة
    //         $newStatus = $newRemainingAmount <= 0
    //             ? AdvanceStatus::Completed
    //             : AdvanceStatus::Partial;

    //         $advance->update([
    //             'remaining_amount' => $newRemainingAmount,
    //             'status' => $newStatus->value,
    //             'last_payment_date' => now(),
    //         ]);

    //         //  تسجيل الدفعة في جدول منفصل (إذا كان موجود)
    //         // $advance->payments()->create([
    //         //     'amount' => $amount,
    //         //     'payment_date' => now(),
    //         //     'notes' => $notes,
    //         //     'processed_by' => auth()->id(),
    //         // ]);

    //         return $advance;
    //     });
    // }




    /*
    |============================================================================
    |============================================================================
    |                          Private methods
    |============================================================================
    |============================================================================
    */
    // فحص أهلية الموظف للحصول على سلفة
    private function validateEmployeeEligibility(int $employeeId): void
    {
        $activeAdvances = Advance::where('employee_id', $employeeId)
            ->whereIn('status', AdvanceStatus::getActiveStatuses())->count();

        if ($activeAdvances > 0) {
            throw ValidationException::withMessages([
                'employee_id' => 'لدى الموظف سلفة نشطة بالفعل'
            ]);
        }
    }

    // إعادة حساب المبلغ المتبقي
    // لم يتم تطبيقها تحتاج مراجعة لتحسب القيم بطريقة صحيحة 
    private function recalculateRemainingAmount(Advance $advance): void
    {
        // إذا تغير المبلغ الأصلي، نحتاج لإعادة حساب المتبقي
        if ($advance->isDirty('amount')) {
            $paidAmount = $advance->amount - $advance->remaining_amount;
            $advance->remaining_amount = $advance->amount - $paidAmount;
            $advance->save();
        }
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
