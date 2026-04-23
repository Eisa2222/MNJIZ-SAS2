<?php

namespace App\Console\Commands\HR\WPS;

use App\Helpers\SettingsHelper;
use App\Services\HR\Payrolls\PayrollCalculatorService;
use App\Models\Hr\Payrolls\WPS\WpsPayroll;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GeneratePayrollCommand extends Command
{
    protected $signature = 'payroll:generate-wps';
    protected $description = 'توليد مسير الرواتب WPS تلقائياً في اليوم المحدد من الإعدادات';

    protected PayrollCalculatorService $calculator;

    public function __construct(PayrollCalculatorService $calculator)
    {
        parent::__construct();
        $this->calculator = $calculator;
    }

    public function handle()
    {
        try {
            $today  = Carbon::now();
            $year   = $today->year;
            $month  = $today->month;

            $disbursementDay = (int) SettingsHelper::get('payroll_disbursement_day');

            if (!$disbursementDay) {
                return Command::FAILURE;
            }

            // التحقق من عدم المعالجة مسبقاً
            if (WpsPayroll::processedFor($year, $month)->exists()) {
                return Command::SUCCESS;
            }

            // توليد الرواتب
            $wpsPayroll = DB::transaction(function () use ($year, $month, $today) {
                // استخدم التاريخ الحالي إذا كان نهاية الشهر في المستقبل
                $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();
                // $runDate    = $endOfMonth->isFuture() ? $today : $endOfMonth;

                // $this->info("جاري توليد مسير الرواتب لتاريخ: {$runDate->format('Y-m-d')}");

                return $this->calculator->processWpsPayroll($endOfMonth);
            });

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("حدث خطأ: " . $e->getMessage());


            return Command::FAILURE;
        }
    }
}
