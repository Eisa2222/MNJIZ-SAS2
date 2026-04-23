<?php

namespace App\Http\Controllers\Hr\Payrolls\WPS;

use App\DataTables\Hr\Payrolls\WPS\WpsDataTable;
use App\Enums\Hr\Payrolls\WPS\WpsStatus;
use App\Exports\WpsPayrollExport;
use App\Helpers\SettingsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\Payrolls\GenerateWpsRequest;
use App\Models\Hr\Employees\Employees;
use App\Models\Hr\Payrolls\WPS\WpsPayroll;
use App\Services\HR\Payrolls\PayrollCalculatorService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel as FacadesExcel;

class WpsPayrollController extends Controller
{
    protected $calculator;
    // private $route = "hr.employees";
    private $page = "hr.payrolls.wps";


    public function __construct(PayrollCalculatorService $calculator)
    {
        $this->calculator = $calculator;
        $this->middleware('can:إدارة المرتبات');
    }

    /*
    |--------------------------------------------------------------------------
    | index
    |--------------------------------------------------------------------------
    */
    public function index(WpsDataTable $dataTable, Request $request)
    {
        try {
            // Statistics
            // $statusCounts = Employees::with('user.roles')->whereHas('user', function ($query) {
            //     $query->where('status', 'active');
            // })->select('hr_status_id')
            //     ->selectRaw('COUNT(*) as count')
            //     ->groupBy('hr_status_id')
            //     ->pluck('count', 'hr_status_id')
            //     ->toArray();


            // $totalEmployees                 =  array_sum($statusCounts);
            // $unavailableEmployees           = $statusCounts[4] ?? 0;
            // $availableEmployees             = $statusCounts[3] ?? 0;
            // $partiallyAvailableEmployees    = $statusCounts[2] ?? 0;
            // $currentEmployees               = $statusCounts[1] ?? 0;

            // // Filters
            // $hrStatus   = SettingsHrStatus::select('id', 'name')->get();
            // $roles      = Role::select('id', 'name')->get();

            $years = WpsPayroll::query()
                ->selectRaw('YEAR(run_date) as year')
                ->distinct()
                ->orderByDesc('year')
                ->pluck('year');

            // الشهور
            $months = [
                1   => 'يناير',
                2   => 'فبراير',
                3   => 'مارس',
                4   => 'أبريل',
                5   => 'مايو',
                6   => 'يونيو',
                7   => 'يوليو',
                8   => 'أغسطس',
                9   => 'سبتمبر',
                10  => 'أكتوبر',
                11  => 'نوفمبر',
                12  => 'ديسمبر',
            ];

            return $dataTable->render($this->page . '.index', compact(
                'years',
                'months',
            ));
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }


    // توليد الرواتب
    public function generateWpsPayroll(GenerateWpsRequest $request)
    {
        $data = $request->validated();

        // التحقق من عدم المعالجة  مسبقا
        $this->guardNotAlreadyProcessed($data['year'], $data['month']);

        // احسب تاريخ الصرف  من يوم  الصرف
        $payDate = $this->calculatePayDate($data['year'], $data['month']);

        $this->guardBeforePayDate($payDate);


        // تحقق من توليد الشهر السابق (ما عدا أول تشغيل)
        // $this->guardPreviousMonthProcessed($data['year'], $data['month']);


        $run = DB::transaction(function () use ($data) {
            $runDate = $this->getEndMonthDate($data['year'], $data['month']);
            return $this->calculator->processWpsPayroll($runDate);
        });

        return redirect()->route('hr.payrolls.wps.index')->with('success', "تم حساب الرواتب بنجاح ");
    }


    /*
    |============================================================================
    | approve wps
    |============================================================================
    */
    public function approve(WpsPayroll $wps_payroll)
    {
        $wps_payroll->update([
            'status'        => WpsStatus::Pending,
            'approved_at'   => now(),
            'approved_by'   => auth()->user()->id,
        ]);

        return redirect()->route('hr.payrolls.wps.index')->with('success', 'تم تصديق المسير بنجاح في انتظار موافقة الادارة .');
    }


    /*
    |--------------------------------------------------------------------------
    | Export PDF
    |--------------------------------------------------------------------------
    */
    public function export_excel(WpsPayroll $wps_payroll)
    {
        $fileName = 'WPS-' . $wps_payroll->reference . '.xlsx';
        return FacadesExcel::download(new WpsPayrollExport($wps_payroll), $fileName);
    }

    /*
    |============================================================================
    |============================================================================
    |                           private function
    |============================================================================
    |============================================================================
    */

    /*
    |============================================================================
    | لتحقق من عدم المعالجة مسبقا
    |============================================================================
    */
    private function guardNotAlreadyProcessed(int $year, int $month): void
    {
        if ($existing = WpsPayroll::processedFor($year, $month)->first()) {
            abort(redirect()->back()->with(
                'error',
                "تمت معالجة الرواتب مسبقًا لشهر {$year}-{$month} (المرجع: {$existing->reference})."
            ));
        }
    }


    /*
    |============================================================================
    | Title
    |============================================================================
    */
    private function calculatePayDate(int $year, int $month): Carbon
    {
        $configured = (int) SettingsHelper::get('payroll_disbursement_day', 0);

        if ($configured < 1 || $configured > 31) {
            $configured = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        }
        // لتحديد القيمة الاقل اذا كان المستخدم قد ادخل في الاعدادات قيمة اكبر من عدد ايام الشهر
        $day = min($configured, Carbon::createFromDate($year, $month, 1)->daysInMonth);

        return Carbon::createFromDate($year, $month, $day)->startOfDay();
    }


    /*
    |============================================================================
    | لتحديد هل حان وقت الصرف ام لا بناءا على يوم الصرف من الاعدادات
    |============================================================================
    */
    private function guardBeforePayDate(Carbon $payDate): void
    {
        if (now()->startOfDay()->lt($payDate)) {
            abort(redirect()->back()->with(
                'error',
                'لا يمكن توليد مسير الرواتب قبل يوم الصرف المحدد (' .
                    $payDate->translatedFormat('d F Y') . ').'
            ));
        }
    }


    /*
    |============================================================================
    | التحقق من انه تم توليد مرتبات الشهور السابقة
    | ما عدا اول مرة
    |============================================================================
    */
    private function guardPreviousMonthProcessed(int $year, int $month): void
    {
        if (! WpsPayroll::exists()) {
            // أول تشغيل، نتجاوز التحقق
            return;
        }

        $prev = Carbon::createFromDate($year, $month, 1)
            ->subMonthNoOverflow()
            ->endOfMonth();

        if (! WpsPayroll::processedFor($prev->year, $prev->month)->exists()) {
            abort(redirect()->back()->with(
                'error',
                'لم يتم توليد مسير رواتب شهر ' .
                    $prev->translatedFormat('F Y') .
                    ' بعد، فيرجى توليدها أولًا قبل توليد مرتبات الشهر التالي.'
            ));
        }
    }


    /*
    |============================================================================
    | تاريخ نهاية الشهر اي تم احتساب الراتب حتي تاريخ
    |============================================================================
    */
    private function getEndMonthDate(int $year, int $month): Carbon
    {
        return Carbon::createFromDate($year, $month, 1)->endOfMonth();
    }
}
