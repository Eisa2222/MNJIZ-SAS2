<?php

namespace App\Console\Commands\Hr\Leave;

use App\Console\Concerns\IteratesTenants;
use App\Models\general_setting\SettingsLeaveType;
use App\Models\Hr\Employees\Employees;
use App\Models\Hr\LeaveBalance;
use App\Models\Hr\LeaveBalanceLog;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DailyLeaveAccrualCommand extends Command
{
    use IteratesTenants;

    protected $signature = 'leave:daily-accrual';
    protected $description = 'Accrue daily leave balances + end-of-year carry-forward for every active tenant.';

    public function handle(): int
    {
        return $this->perTenant(function (Tenant $tenant) {
            $today        = Carbon::today();
            $currentYear  = $today->year;
            $previousYear = $currentYear - 1;
            $isNewYear    = $today->month == 1 && $today->day == 1;

            DB::transaction(function () use ($today, $currentYear, $previousYear, $isNewYear) {
                // SettingsLeaveType is currently a shared/global lookup — no
                // tenant_id. If a tenant has overridden types, they'd need a
                // per-tenant copy (Phase 7 migration concern).
                $carryForwardableLeaveTypes = SettingsLeaveType::where('is_carry_forwardable', true)
                    ->where('status', 'active')
                    ->whereNotNull('days')
                    ->where('days', '>', 0)
                    ->get();

                if ($carryForwardableLeaveTypes->isEmpty()) {
                    return;
                }

                if ($isNewYear) {
                    $this->processYearEndCarryForward($carryForwardableLeaveTypes, $currentYear, $previousYear);
                }

                $this->processDailyAccrual($carryForwardableLeaveTypes, $today, $currentYear);
            });
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Process Year End Carry Forward  
    |--------------------------------------------------------------------------
    */
    private function processYearEndCarryForward($leaveTypes, $currentYear, $previousYear)
    {
        $existingRecords = LeaveBalance::where('year', $currentYear)
            ->whereIn('leave_type_id', $leaveTypes->pluck('id'))
            ->where('total_days', '>', 0)
            ->exists();

        if ($existingRecords) {
            return;
        }

        foreach ($leaveTypes as $leaveType) {
            $previousYearBalances = LeaveBalance::where('leave_type_id', $leaveType->id)
                ->where('year', $previousYear)
                ->where('remaining_days', '>', 0)
                ->with('employee')
                ->get();

            foreach ($previousYearBalances as $previousBalance) {
                if (!$previousBalance->employee) {
                    continue;
                }

                $employee = $previousBalance->employee;
                $carriedAmount = $previousBalance->remaining_days;

                $currentYearBalance = LeaveBalance::where([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                    'year' => $currentYear,
                ])->first();

                if ($currentYearBalance) {
                    if ($currentYearBalance->total_days > 0) {
                        continue;
                    }

                    // تسجيل عملية الترحيل للرصيد الموجود
                    $this->logCarryForwardOperation(
                        $currentYearBalance,
                        0, // old total days
                        $carriedAmount, // new total days
                        0, // old remaining days
                        $carriedAmount, // new remaining days
                        $previousYear
                    );

                    $currentYearBalance->update([
                        'total_days' => $carriedAmount,
                        'remaining_days' => $carriedAmount,
                    ]);
                } else {
                    // إنشاء رصيد جديد مع الترحيل
                    $newBalance = LeaveBalance::create([
                        'employee_id' => $employee->id,
                        'leave_type_id' => $leaveType->id,
                        'year' => $currentYear,
                        'total_days' => $carriedAmount,
                        'used_days' => 0.0000,
                        'remaining_days' => $carriedAmount,
                        'last_accrued_at' => null,
                        'last_updated_by' => null,
                    ]);

                    // تسجيل عملية إنشاء رصيد جديد مع الترحيل
                    $this->logCarryForwardOperation(
                        $newBalance,
                        0, // old total days (لا يوجد رصيد سابق)
                        $carriedAmount, // new total days
                        0, // old remaining days (لا يوجد رصيد سابق)
                        $carriedAmount, // new remaining days
                        $previousYear
                    );
                }
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Process Daily Accrual  
    |--------------------------------------------------------------------------
    */
    private function processDailyAccrual($leaveTypes, $today, $currentYear)
    {
        Employees::active()->chunk(100, function ($employees) use ($leaveTypes, $today, $currentYear) {
            foreach ($employees as $employee) {
                foreach ($leaveTypes as $leaveType) {
                    $this->processEmployeeLeaveAccrual($employee, $leaveType, $today, $currentYear);
                }
            }
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Process Employee Leave Accrual  
    |--------------------------------------------------------------------------
    */
    private function processEmployeeLeaveAccrual($employee, $leaveType, $today, $currentYear)
    {
        try {
            $balance = LeaveBalance::firstOrCreate(
                [
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                    'year' => $currentYear,
                ],
                [
                    'total_days' => 0.0000,
                    'used_days' => 0.0000,
                    'remaining_days' => 0.0000,
                    'last_accrued_at' => null,
                    'last_updated_by' => null,
                ]
            );

            // إذا تم إنشاء رصيد جديد (الموظف لا يملك رصيد من قبل)
            if ($balance->wasRecentlyCreated) {
                $this->logZeroBalanceCreation($balance);
            }

            $lastAccruedDate = $balance->last_accrued_at ? Carbon::parse($balance->last_accrued_at) : null;

            if ($lastAccruedDate && $lastAccruedDate->isSameDay($today)) {
                return;
            }

            $annualDays = $this->calculateAnnualDays($employee, $leaveType, $today);
            $dailyRate = $this->calculateDailyRate($annualDays, $today);

            // حفظ القيم القديمة للتسجيل
            $oldTotalDays = $balance->total_days;
            $oldRemainingDays = $balance->remaining_days;

            // تحديث الرصيد
            $balance->increment('total_days', $dailyRate);
            $balance->increment('remaining_days', $dailyRate);
            $balance->update(['last_accrued_at' => $today->toDateString()]);

            // تسجيل عملية الترصيد اليومي
            $this->logDailyAccrualOperation(
                $balance,
                $oldTotalDays,
                $balance->total_days,
                $oldRemainingDays,
                $balance->remaining_days,
                $dailyRate,
                $annualDays
            );
        } catch (\Exception $e) {
            // Silent failure
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Log Daily Accrual Operation
    |--------------------------------------------------------------------------
    */
    private function logDailyAccrualOperation($balance, $oldTotalDays, $newTotalDays, $oldRemainingDays, $newRemainingDays, $dailyRate, $annualDays)
    {
        LeaveBalanceLog::create([
            'leave_balance_id' => $balance->id,
            'employee_id' => $balance->employee_id,
            'year' => $balance->year,
            'user_id' => null, // عملية تلقائية
            'action' => 'daily_accrual',
            'old_total_days' => $oldTotalDays,
            'new_total_days' => $newTotalDays,
            'old_used_days' => $balance->used_days,
            'new_used_days' => $balance->used_days,
            'old_remaining_days' => $oldRemainingDays,
            'new_remaining_days' => $newRemainingDays,
            'notes' => "ترصيد يومي تلقائي - المعدل اليومي: {$dailyRate} - الرصيد السنوي: {$annualDays} يوم"
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Log Carry Forward Operation
    |--------------------------------------------------------------------------
    */
    private function logCarryForwardOperation($balance, $oldTotalDays, $newTotalDays, $oldRemainingDays, $newRemainingDays, $previousYear)
    {
        $carriedAmount = $newRemainingDays - $oldRemainingDays;

        LeaveBalanceLog::create([
            'leave_balance_id' => $balance->id,
            'employee_id' => $balance->employee_id,
            'year' => $balance->year,
            'user_id' => null, // عملية تلقائية
            'action' => 'carry_forward',
            'old_total_days' => $oldTotalDays,
            'new_total_days' => $newTotalDays,
            'old_used_days' => 0,
            'new_used_days' => 0,
            'old_remaining_days' => $oldRemainingDays,
            'new_remaining_days' => $newRemainingDays,
            'notes' => "ترحيل تلقائي من سنة {$previousYear} - المبلغ المرحل: {$carriedAmount} يوم"
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Log Zero Balance Creation
    |--------------------------------------------------------------------------
    */
    private function logZeroBalanceCreation($balance)
    {
        LeaveBalanceLog::create([
            'leave_balance_id' => $balance->id,
            'employee_id' => $balance->employee_id,
            'year' => $balance->year,
            'user_id' => null, // عملية تلقائية
            'action' => 'zero_balance_creation',
            'old_total_days' => 0,
            'new_total_days' => 0,
            'old_used_days' => 0,
            'new_used_days' => 0,
            'old_remaining_days' => 0,
            'new_remaining_days' => 0,
            'notes' => 'إنشاء رصيد صفري تلقائي للموظف الجديد'
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Calculate Annual Days
    |--------------------------------------------------------------------------
    */
    private function calculateAnnualDays($employee, $leaveType, $today)
    {
        $annualDays = $leaveType->days;

        if (
            $employee->contract_start_date &&
            $leaveType->service_years_threshold > 0 &&
            $leaveType->days_after_threshold > 0
        ) {

            $yearsService = Carbon::parse($employee->contract_start_date)->diffInYears($today);

            if ($yearsService >= $leaveType->service_years_threshold) {
                $annualDays = $leaveType->days_after_threshold;
            }
        }

        return $annualDays;
    }


    /*
    |--------------------------------------------------------------------------
    | Calculate Daily Rate  
    |--------------------------------------------------------------------------
    */
    private function calculateDailyRate($annualDays, $today)
    {
        $daysInYear = $today->isLeapYear() ? 366 : 365;
        return round($annualDays / $daysInYear, 4);
    }
}
