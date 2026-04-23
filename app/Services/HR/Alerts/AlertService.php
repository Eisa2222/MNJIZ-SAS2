<?php
// app/Services/HR/Alerts/AlertService.php

namespace App\Services\HR\Alerts;

use App\Contracts\ErrorHandlerInterface;
use App\Enums\Hr\Alert\AlertStatus;
use App\Enums\Hr\Alert\AlertType;
use App\Models\Hr\Alert\Alert;
use App\Models\Hr\Employees\Employees;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AlertService
{
    public function __construct(private ErrorHandlerInterface $errorHandler) {}

    public function create($employee_id, $type): void
    {
        try {
            $this->errorHandler->execute(function () use ($employee_id, $type) {
                return DB::transaction(function () use ($employee_id, $type) {
                    $alert = new Alert([
                        'employee_id'    => $employee_id,
                        'type'           => $type,
                        'status'         => AlertStatus::New,
                    ]);
                    $alert->save();
                });
            }, 'حدث خطأ أثناء حفظ التنبيه');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function processExpiryReminders(): int
    {
        $totalCreated = 0;
        $today = Carbon::today();

        // ✅ مباشرة من الـ Enum - بدون Config::get()
        foreach (AlertType::cases() as $alertType) {
            $fieldName = $alertType->getFieldName();

            $employees = Employees::active()
                ->whereNotNull($fieldName)
                ->whereDate($fieldName, '<=', $today)
                ->get();

            foreach ($employees as $employee) {
                $exists = Alert::where('employee_id', $employee->id)
                    ->where('type', $alertType->value)
                    ->where('status', AlertStatus::New->value)
                    ->whereDate('created_at', Carbon::today()) // نفس اليوم
                    ->exists();

                if (!$exists) {
                    $this->create($employee->id, $alertType->value);
                    $totalCreated++;
                }
            }
        }

        return $totalCreated;
    }
}
