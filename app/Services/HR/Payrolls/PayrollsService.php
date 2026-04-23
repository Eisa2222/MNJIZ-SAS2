<?php

namespace App\Services\HR\Payrolls;

use App\Helpers\SettingsHelper;
use App\Models\Hr\Employees\Employees;
use App\Models\Hr\Employees\EmployeeSalaryHistory;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class PayrollsService
{
    /*
    |--------------------------------------------------------------------------
    | لجلب تفاصيل الراتب حسب السجل
    |--------------------------------------------------------------------------
    */
    public function getSalaryBreakdownAt(int $employeeId, Carbon $date): array
    {
        $h = EmployeeSalaryHistory::where('employee_id', $employeeId)
            ->where('effective_from', '<=', $date)
            ->orderByDesc('effective_from')
            ->orderByDesc('created_at')
            ->first();

        if ($h) {
            return [
                'basic'      => (float) $h->basic_salary,
                'transport'  => (float) $h->transportation_allowance,
                'housing'    => (float) $h->housing_allowance,
                'other'      => (float) $h->other_allowances,
            ];
        }

        // احتياطى: آخر قيم حالية من جدول employees
        $e = Employees::find($employeeId);

        return [
            'basic'     => (float) ($e->basic_salary             ?? 0),
            'transport' => (float) ($e->transportation_allowance ?? 0),
            'housing'   => (float) ($e->housing_allowance        ?? 0),
            'other'     => (float) ($e->other_allowances         ?? 0),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | حساب عدد أيام العمل الفعلية في الشهر مع استبعاد أيام العطل الأسبوعية
    |--------------------------------------------------------------------------
    */
    public function getWorkingDaysInMonth(?Carbon $date = null): int
    {
        // تحديد تاريخ اليوم لاخذ الشهر منه
        $ref = $date ? $date->copy() : Carbon::now();

        // جلب اول و اخر الشهر 
        $start = $ref->copy()->startOfMonth();
        $end   = $ref->copy()->endOfMonth();

        // جلب ايام الاجازة الاسبوعية او تحديدها افتراضيا 
        $raw         = SettingsHelper::get('weekly_days_off');
        $weekendDays = is_string($raw) ? json_decode($raw, true) : $raw;
        $weekendDays = array_filter((array) $weekendDays, fn($d) => in_array(
            strtolower($d),
            ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']
        ));
        // اذا لم يكن هناك اجازة اسبوعية في الاعدادات
        if (empty($weekendDays)) {
            $weekendDays = ['friday', 'saturday'];
        }

        // حوّل الأسماء لأرقام Carbon
        $dayMap = [
            'sunday'    => Carbon::SUNDAY,
            'monday'    => Carbon::MONDAY,
            'tuesday'   => Carbon::TUESDAY,
            'wednesday' => Carbon::WEDNESDAY,
            'thursday'  => Carbon::THURSDAY,
            'friday'    => Carbon::FRIDAY,
            'saturday'  => Carbon::SATURDAY,
        ];

        $weekendNums = array_map(fn($d) => $dayMap[strtolower($d)], $weekendDays);

        // فلترة الايام واستخراج ايام الاجازة منها 
        $period = CarbonPeriod::create($start, $end);
        $working = array_filter(
            iterator_to_array($period),
            fn(Carbon $day) => !in_array($day->dayOfWeek, $weekendNums, true)
        );

        //   عدد أيام العمل
        return count($working);
    }


    /*
    |--------------------------------------------------------------------------
    |  حساب المعدل اليومي للراتب
    |--------------------------------------------------------------------------
    */
    public function calculateDailyRate(float $grossMonthlySalary, ?Carbon $date = null): float
    {
        $days = $this->getWorkingDaysInMonth($date);
        return $days > 0 ? $grossMonthlySalary / $days : 0;
    }



    /*
    |--------------------------------------------------------------------------
    |   خصم من المرتب بناءا على عدد الايام 
    |--------------------------------------------------------------------------
    */
    public function calculateDaysDeduction(float $grossMonthlySalary, int $daysToDeduct, ?Carbon $date = null): float
    {
        return $this->calculateDailyRate($grossMonthlySalary, $date) * $daysToDeduct;
    }


    /*
    |--------------------------------------------------------------------------
    |   خصم من المرتب بنسبة معينة 
    |--------------------------------------------------------------------------
    */
    public function calculatePercentageDeduction(float $grossMonthlySalary, float $percentage, ?Carbon $date = null): float
    {
        //  الأجر اليومي 
        $dailyRate = $this->calculateDailyRate($grossMonthlySalary, $date);

        // طبق النسبة على الأجر اليومي
        return $dailyRate * ($percentage / 100);
    }
}
