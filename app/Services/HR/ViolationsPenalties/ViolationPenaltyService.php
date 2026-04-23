<?php

namespace App\Services\HR\ViolationsPenalties;

use App\Contracts\ErrorHandlerInterface;
use App\Data\Hr\ViolationsPenalties\ViolationsPenaltiesData;
use App\Enums\Hr\ViolationsPenalties\ViolationAppealStatus;
use App\Enums\Hr\ViolationsPenalties\ViolationStatus;
use App\Models\Hr\Violations\Violation;
use App\Models\general_setting\SettingsViolation;
use App\Models\Hr\Employees\Employees;
use App\Models\Hr\Violations\ViolationAppeal;
use App\Models\Hr\Violations\ViolationExecution;
use App\Services\HR\Payrolls\PayrollsService;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ViolationPenaltyService
{
    public function __construct(private ErrorHandlerInterface $errorHandler, private PayrollsService $payrollsService) {}

    // Create
    public function createViolation(ViolationsPenaltiesData $dto)
    {
        return $this->errorHandler->execute(fn() => DB::transaction(function () use ($dto) {
            $settings = SettingsViolation::findOrFail($dto->settings_violation_id);

            // تحقق من وجود مخالفة مشابهة
            $exists = Violation::ofEmployee($dto->employee_id)
                ->ofType($dto->settings_violation_id)
                ->pendingOrUnderAppeal()
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'settings_violation_id' => 'هناك مخالفة مشابهة قيد الانتظار أو التظلم.',
                ]);
            }

            // حساب التكرار
            $occurrence = Violation::ofEmployee($dto->employee_id)
                ->ofType($dto->settings_violation_id)
                ->notCancelledOrRejected()
                ->lockForUpdate()
                ->count() + 1;

            $data = $dto->toArray() + [
                'occurrence'       => $occurrence,
                'penalty_text'     => $this->calculatePenalty($settings, $occurrence),
                'reference_number' => $this->generateReferenceNumber(),
                'status'           => $dto->is_appealable ? ViolationStatus::UnderAppeal : ViolationStatus::Pending,
                'created_by'       => $this->currentEmployeeId(),
            ];


            Violation::create($data);
        }), 'حدث خطأ أثناء إنشاء المخالفة');
    }

    // Update
    public function updateViolation(int $id, ViolationsPenaltiesData $dto)
    {
        return $this->errorHandler->execute(fn() => DB::transaction(function () use ($id, $dto) {

            $violation = Violation::findOrFail($id);

            // التأكد من إمكانية التعديل حسب الحالة
            if (!ViolationStatus::from($violation->status->value)->canEditOrDelete()) {
                throw ValidationException::withMessages([
                    'status' => 'لا يمكن تعديل المخالفة إلا إذا كانت في حالة انتظار الاعتماد.',
                ]);
            }

            // فحص وجود مخالفة مشابهة قيد الانتظار أو التظلم (باستثناء الحالية)
            $exists = Violation::ofEmployee($dto->employee_id)
                ->ofType($dto->settings_violation_id)
                ->pendingOrUnderAppeal()
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'settings_violation_id' => 'هناك مخالفة مشابهة قيد الانتظار أو التظلم.',
                ]);
            }

            // إعادة حساب التكرار إذا تغير الموظف أو نوع المخالفة
            $needsRecount =
                $violation->employee_id !== $dto->employee_id ||
                $violation->settings_violation_id !== $dto->settings_violation_id;

            if ($needsRecount) {
                $occurrence = Violation::ofEmployee($dto->employee_id)
                    ->ofType($dto->settings_violation_id)
                    ->notCancelledOrRejected()
                    ->where('id', '!=', $id)
                    ->lockForUpdate()
                    ->count() + 1;
            } else {
                $occurrence = $violation->occurrence;
            }

            // جلب إعدادات المخالفة لإعادة حساب نص العقوبة
            $settings = SettingsViolation::findOrFail($dto->settings_violation_id);

            // تجهيز بيانات التحديث
            $data = $dto->toArray() + [
                'updated_by'   => $this->currentEmployeeId(),
                'occurrence'   => $occurrence,
                'penalty_text' => $this->calculatePenalty($settings, $occurrence),
                'status'       => $dto->is_appealable ? ViolationStatus::UnderAppeal : ViolationStatus::Pending,
            ];

            $violation->update($data);
        }), 'حدث خطأ أثناء تحديث المخالفة');
    }

    // Delete
    public function deleteViolation(int $id)
    {
        return $this->errorHandler->execute(fn() => DB::transaction(function () use ($id) {
            $violation = Violation::findOrFail($id);

            if (!ViolationStatus::from($violation->status->value)->canEditOrDelete()) {
                throw ValidationException::withMessages([
                    'status' => 'لا يمكن حذف المخالفة إلا إذا كانت في حالة انتظار الاعتماد.',
                ]);
            }

            $violation->delete();
        }), 'حدث خطأ أثناء حذف المخالفة');
    }

    // Apply
    public function applyPenalty(int $id, ?string $notes = null, ?\Illuminate\Http\UploadedFile $file = null)
    {
        return $this->errorHandler->execute(fn() => DB::transaction(function () use ($id, $notes, $file) {

            $violation = Violation::findOrFail($id);

            if (!ViolationStatus::from($violation->status->value)->canApplyPenalty()) {
                throw ValidationException::withMessages([
                    'status' => 'لا يمكن تطبيق العقوبة على مخالفة بهذه الحالة.',
                ]);
            }

            // حساب الخصم
            $costData = $this->calculateViolationValue($violation->id);

            $execution = new ViolationExecution(array_merge($costData, [
                'execution_date' => now(),
                'executed_by'    => $this->currentEmployeeId(),
                'notes'          => $notes,
            ]));

            $violation->execution()->save($execution);

            if ($file) {
                $this->handleAttachmentExecution($execution, $file);
            }

            $violation->update([
                'status'      => ViolationStatus::Approved,
                'reviewed_by' => $this->currentEmployeeId(),
                'reviewed_at' => now(),
            ]);
        }), 'حدث خطأ أثناء تنفيذ العقوبة');
    }

    public function cancelPenalty(int $id): void
    {
        $this->errorHandler->execute(fn() => DB::transaction(function () use ($id) {

            $violation = Violation::findOrFail($id);

            if (!ViolationStatus::from($violation->status->value)->canApplyPenalty()) {
                throw ValidationException::withMessages([
                    'status' => 'لا يمكن إلغاء العقوبة لهذه الحالة.'
                ]);
            }

            $violation->update([
                'status'      => ViolationStatus::Cancelled,
                'reviewed_by' => $this->currentEmployeeId(),
                'reviewed_at' => now(),
            ]);

            if ($violation->execution) {
                $violation->execution->delete();
            }
        }), 'حدث خطأ أثناء إلغاء العقوبة');
    }

    public function getOccurrenceCount(int $employeeId, int $settingsId, ?int $excludeId = null): array
    {
        return $this->errorHandler->execute(fn() => DB::transaction(function () use ($employeeId, $settingsId, $excludeId) {
            // التحقق من وجود الموظف
            if (!Employees::find($employeeId)) {
                throw ValidationException::withMessages([
                    'employee_id' => 'الموظف غير موجود.',
                ]);
            }

            // التحقق من وجود نوع المخالفة
            $settings = SettingsViolation::find($settingsId);
            if (!$settings) {
                throw ValidationException::withMessages([
                    'settings_violation_id' => 'نوع المخالفة غير موجود.',
                ]);
            }

            // استعلام أساسي - استبعاد المخالفات الملغية والمرفوضة
            $query = Violation::where('employee_id', $employeeId)
                ->where('settings_violation_id', $settingsId)
                ->whereNotIn('status', [
                    ViolationStatus::Cancelled->value,
                    ViolationStatus::Rejected->value
                ]);

            // استبعاد المخالفة الحالية عند التعديل
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            // احتساب عدد المخالفات
            $count = $query->count();
            $occurrence = $count + 1;

            // حساب العقوبة المناسبة
            $penalty = $this->calculatePenalty($settings, $occurrence);

            // العقوبة المنسقة (إذا كانت متوفرة)
            $formattedPenalty = $this->getFormattedPenalty($settings, $occurrence);

            return [
                'count' => $count,
                'occurrence' => $occurrence,
                'penalty' => $penalty,
                'formatted_penalty' => $formattedPenalty,
                'violation_type' => $settings->description,
                'category' => $settings->category->name ?? null,
            ];
        }), 'حدث خطأ أثناء حذف المخالفة');
    }


    private function getFormattedPenalty(SettingsViolation $settings, int $occurrence): string
    {
        $formattedPenalty = match ($occurrence) {
            1 => $settings->formatted_penalty_first ?? $settings->penalty_first,
            2 => $settings->formatted_penalty_second ?? $settings->penalty_second,
            3 => $settings->formatted_penalty_third ?? $settings->penalty_third,
            default => $settings->formatted_penalty_fourth ?? $settings->penalty_fourth,
        };

        // إضافة الخصم الإضافي إن وجد
        if (!empty($settings->extra_deduction)) {
            $formattedPenalty .= ' ' . $settings->extra_deduction;
        }

        return $formattedPenalty ?? 'لم يتم تحديد عقوبة';
    }



    /*
    |============================================================================
    |============================================================================
    |                               Appeal
    |============================================================================
    |============================================================================
    */
    // تقديم التظلم بالنسبة للموظف
    public function submitAppeal(int $violationId, string $appealReason)
    {
        return $this->errorHandler->execute(fn() => DB::transaction(function () use ($violationId, $appealReason) {

            $violation = Violation::findOrFail($violationId);

            if ($violation->employee_id !== $this->currentEmployeeId()) {
                throw ValidationException::withMessages([
                    'violation' => 'لا يمكنك تقديم تظلم على مخالفة لا تخصك.',
                ]);
            }

            // التحقق من أن المخالفة قابلة للتظلم
            if (!$violation->is_appealable) {
                throw ValidationException::withMessages([
                    'violation' => 'هذه المخالفة غير قابلة للتظلم.',
                ]);
            }

            // التحقق من حالة المخالفة
            if (!in_array($violation->status, [ViolationStatus::UnderAppeal])) {
                throw ValidationException::withMessages([
                    'violation' => 'لا يمكن تقديم تظلم على مخالفة بهذه الحالة.',
                ]);
            }

            // التحقق من وجود تظلم سابق
            if ($violation->appeal) {
                throw ValidationException::withMessages([
                    'appeal' => 'تم تقديم تظلم على هذه المخالفة مسبقاً.',
                ]);
            }

            // التحقق من انتهاء مهلة التظلم
            if ($violation->appeal_days > 0) {
                $deadlineDate = $violation->created_at->addDays($violation->appeal_days);
                if (now()->isAfter($deadlineDate)) {
                    throw ValidationException::withMessages([
                        'deadline' => 'انتهت مهلة تقديم التظلم على هذه المخالفة.',
                    ]);
                }
            }

            ViolationAppeal::create([
                'violation_id'      => $violationId,
                'appeal_reason'     => $appealReason,
                'appeal_date'       => now(),
                'status'            => ViolationAppealStatus::Pending,
            ]);

            $violation->update([
                'status'    => ViolationStatus::AppealSubmitted,
            ]);
        }), 'حدث خطأ أثناء تقديم التظلم');
    }

    // الرد على التظلم بالنسبة للادارة
    public function respondToAppeal(int $appealId, string $status, string $response): void
    {
        $this->errorHandler->execute(fn() => DB::transaction(function () use ($appealId, $status, $response) {

            $appeal = ViolationAppeal::with('violation')->findOrFail($appealId);

            // التحقق من حالة التظلم
            if ($appeal->status !== ViolationAppealStatus::Pending) {
                throw ValidationException::withMessages([
                    'appeal' => 'تم الرد على هذا التظلم مسبقاً.',
                ]);
            }

            // التحقق من صحة الحالة المرسلة
            if (!in_array($status, ['approved', 'rejected'])) {
                throw ValidationException::withMessages([
                    'status' => 'حالة التظلم غير صحيحة.',
                ]);
            }

            // تحديث التظلم
            $appeal->update([
                'status' => ViolationAppealStatus::from($status),
                'response' => $response,
                'reviewed_by' => $this->currentEmployeeId(),
                'reviewed_at' => now(),
            ]);

            $violation = $appeal->violation;

            if ($status === 'approved') {
                $this->cancelPenalty($violation->id);
            } else {
                $this->applyPenalty($violation->id, $response);
            }
        }), 'حدث خطأ أثناء الرد على التظلم');
    }


    /*
    |============================================================================
    |============================================================================
    |                           Private Methods
    |============================================================================
    |============================================================================
    */
    private function calculatePenalty(SettingsViolation $violation, int $occurrence): string
    {
        $penalty = match (true) {
            $occurrence === 1 => $violation->penalty_first,
            $occurrence === 2 => $violation->penalty_second,
            $occurrence === 3 => $violation->penalty_third,
            default            => $violation->penalty_fourth,
        };

        if ($violation->extra_deduction) {
            $penalty .= ' ' . $violation->extra_deduction;
        }

        return $penalty ?? 'لم يتم تحديد عقوبة';
    }

    private function generateReferenceNumber(): string
    {
        return DB::transaction(function () {
            $lastViolation = Violation::lockForUpdate()
                ->orderBy('id', 'desc')
                ->first();

            if ($lastViolation) {
                preg_match('/V-(\d+)/', $lastViolation->reference_number, $matches);
                $lastNumber = isset($matches[1]) ? (int)$matches[1] : 0;
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $referenceNumber = 'V-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            while (Violation::where('reference_number', $referenceNumber)->exists()) {
                $nextNumber++;
                $referenceNumber = 'V-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            }

            return $referenceNumber;
        });
    }

    // حساب قسمة المخالفة
    private function calculateViolationValue(int $violationId): array
    {
        $violation = Violation::findOrFail($violationId);

        $salary    = $this->payrollsService->getSalaryBreakdownAt(
            $violation->employee_id,
            $violation->violation_date
        );

        if ($salary <= 0) {
            throw new \Exception('لا يمكن حساب العقوبة: راتب الموظف غير محدد أو يساوي صفر.');
        }

        // الراتب بكامل البدلات
        $grossSalary = array_sum($salary);

        return $this->parseExecutionFromPenaltyText(
            $violation->penalty_text,
            $grossSalary,
            $violation->violation_date
        );
    }

    //تحليل نص التنفيذ وإرجاع نوع التنفيذ وقيم الخصم
    private function parseExecutionFromPenaltyText(string $penaltyText, float $grossSalary, Carbon $executionDate): array
    {
        $executionType       = 'other';
        $deductionDays       = null;
        $deductionPercentage = null;
        $deductionAmount     = null;

        if (strpos($penaltyText, ':') !== false) {
            list($type, $value) = explode(':', $penaltyText, 2);

            switch ($type) {
                case 'percentage':
                    $executionType       = 'deduction';
                    $deductionPercentage = $value;
                    break;
                case 'days':
                    $executionType = 'deduction';
                    $deductionDays = (int) $value;
                    break;
                case 'warning':
                    $executionType = 'warning';
                    break;
                case 'ban':
                    $executionType = 'deprivation';
                    break;
                case 'termination':
                    $executionType = 'dismissal';
                    break;
            }
        }

        if ($executionType === 'deduction') {
            if ($deductionPercentage !== null) {
                $deductionAmount = $this->payrollsService->calculatePercentageDeduction(
                    $grossSalary,
                    $deductionPercentage,
                    $executionDate
                );
            } elseif ($deductionDays !== null) {
                $deductionAmount = $this->payrollsService->calculateDaysDeduction(
                    $grossSalary,
                    $deductionDays,
                    $executionDate
                );
            }
        }

        return [
            'execution_type'       => $executionType,
            'deduction_days'       => $deductionDays,
            'deduction_percentage' => $deductionPercentage,
            'deduction_amount'     => $deductionAmount,
        ];
    }

    private function handleAttachmentExecution($violationExecution, UploadedFile $file)
    {
        try {
            $fileName = 'violation_' . $violationExecution->id . '_' . time() . '.' . $file->getClientOriginalExtension();

            $path     = $file->storeAs('violations/attachments', $fileName, 'public');

            $violationExecution->update(['attachment' => $path]);
        } catch (\Exception $e) {
            return null;
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
