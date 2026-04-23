<?php

namespace App\Http\Controllers\ElectronicServices\ViolationsPenalties;

use App\DataTables\ElectronicServices\ViolationsPenalties\EmployeeViolationsPenaltiesDataTable;
use App\Enums\Hr\ViolationsPenalties\ViolationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ElectronicServices\ViolationsPenalties\AppealRequest;
use App\Models\general_setting\SettingsViolation;
use App\Models\Hr\Violations\Violation;
use App\Services\HR\ViolationsPenalties\ViolationPenaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class EmployeeViolationsController extends Controller
{
    private $route = "account.electronic-services.violations-penalties";
    private $page   = "electronic_services.violations_penalties";


    public function __construct(private ViolationPenaltyService $penaltyService)
    {
        $this->middleware('can:الإنتهاكات و العقوبات الخاصة بي');
    }


    public function index(EmployeeViolationsPenaltiesDataTable $dataTable)
    {
        try {
            // Statistics
            $statusCounts = Violation::query()->where('employee_id', Auth::user()->employee->id)
                ->select('status')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $totalViolation = array_sum($statusCounts);
            $pendingViolation = $statusCounts[ViolationStatus::Pending->value] ?? 0;
            $approvedViolation = $statusCounts[ViolationStatus::Approved->value] ?? 0;
            $rejectedViolation = $statusCounts[ViolationStatus::Rejected->value] ?? 0;

            // Filters
            $violationsSettings     = SettingsViolation::select('id', 'description')->get();
            $Violationtatus         = ViolationStatus::options();

            return $dataTable->render($this->page . '.index', compact(
                // Statistics
                'totalViolation',
                'pendingViolation',
                'approvedViolation',
                'rejectedViolation',
                // Filters
                'violationsSettings',
                'Violationtatus'
            ));
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }



    public function show(Violation $violation)
    {
        if ($violation->employee_id !== Auth::user()->employee->id) {
            abort(404);
        }
        return view('electronic_services.violations_penalties.show', compact('violation'));
    }


    public function submitAppeal(AppealRequest $request, $id)
    {
        try {
            $data = $request->validated();

            $this->penaltyService->submitAppeal($id, $data['appeal_reason']);

            return redirect()->back()->with('success', 'تم تقديم التظلم بنجاح.');
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
