<?php

namespace App\Http\Controllers\Hr\Deductions;

use App\Data\Hr\Deductions\DeductionData;
use App\DataTables\Hr\Deduction\DeductionsDataTable;
use App\Enums\Hr\Deduction\DeductionStatus;
use App\Enums\Hr\Deduction\DeductionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\Deductions\DeductionRequest;
use App\Models\Hr\Deductions\Deduction;
use App\Models\Hr\Employees\Employees;
use App\Services\HR\Deductions\DeductionService;
use Illuminate\Http\Request;

class DeductionController extends Controller
{
    public function __construct(private DeductionService $service)
    {
        $this->middleware('can:إدارة الخصومات')->only(['index', 'show']);
        $this->middleware('can:إضافة خصم مالي')->only(['create', 'store']);
        $this->middleware('can:تعديل خصم مالي')->only(['edit', 'update']);
        $this->middleware('can:حذف خصم مالي')->only(['destroy']);
    }

    /*
    |============================================================================
    | Index
    |============================================================================
    */
    public function index(DeductionsDataTable $dataTable, Request $request)
    {

        try {
            if ($request->ajax()) return $dataTable->ajax();

            // Statistics
            $statusCounts = Deduction::query()
                ->select('status')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $totalDeduction = array_sum($statusCounts);
            $pendingDeduction = $statusCounts[DeductionStatus::Pending->value] ?? 0;
            $approvedDeduction = $statusCounts[DeductionStatus::Approved->value] ?? 0;
            $rejectedDeduction = $statusCounts[DeductionStatus::Rejected->value] ?? 0;

            // Filters
            $employees              = Employees::active()->select('id', 'name', 'nickname')->get();
            $deductionTypes         = DeductionType::options();
            $deductionStatus        = DeductionStatus::options();


            return $dataTable->render('hr.deductions.index', compact(
                // Statistics
                'totalDeduction',
                'pendingDeduction',
                'approvedDeduction',
                'rejectedDeduction',
                // Filters
                'employees',
                'deductionTypes',
                'deductionStatus'
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء جلب البيانات. يرجى المحاولة لاحقاً.');
        }
    }


    /*
    |============================================================================
    | create
    |============================================================================
    */
    public function create()
    {
        $employees       = Employees::active()->select('id', 'name', 'nickname')->get();
        $deductionTypes  = DeductionType::options();
        return view('hr.deductions.create', compact('employees', 'deductionTypes'));
    }

    /*
    |============================================================================
    | store
    |============================================================================
    */
    public function store(DeductionRequest $request)
    {
        try {
            $dto = new DeductionData($request->validated());

            $this->service->createDeduction($dto);

            return redirect()->route('hr.deductions.index')->with('success', 'تم إضافة الخصم المالي بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }


    /*
    |============================================================================
    | show
    |============================================================================
    */
    public function show(Deduction $deduction)
    {
        $deduction->load([
            'employee',
            'createdBy',
            'updatedBy',
            'approvalRequest.requestLevels.employee',
        ]);

        // -- Approvel --//
        $approvalStages = $deduction->approvalRequest ? $deduction->approvalRequest->getApprovalStages() : [];
        $isAutoApproved = empty($approvalStages) && ($deduction->status === DeductionStatus::Approved);
        // -- Approvel --//
        return view('hr.deductions.show', compact('deduction', 'approvalStages', 'isAutoApproved'));
    }





    /*
    |============================================================================
    | edit
    |============================================================================
    */
    public function edit(Deduction $deduction)
    {
        if (!$deduction->status->canEdit()) {
            return redirect()
                ->route('hr.deductions.index')
                ->with('error', ' لا يمكن تعديل الخصم المالي في  حالة  ' . $deduction->status->label());
        }

        $employees   = Employees::active()->select('id', 'name', 'nickname')->get();

        $deductionTypes = DeductionType::options();

        return view('hr.deductions.edit', compact('deduction', 'employees', 'deductionTypes'));
    }


    /*
    |============================================================================
    | Update
    |============================================================================
    */
    public function update(DeductionRequest $request, int $id)
    {
        try {
            $dto = new DeductionData($request->validated());

            $this->service->updateDeduction($id, $dto);

            return redirect()->route('hr.deductions.index')->with('success', 'تم تحديث الخصم المالي بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }


    /*
    |============================================================================
    | Delete
    |============================================================================
    */
    public function destroy(int $id)
    {
        try {
            $this->service->deleteDeduction($id);

            return redirect()->route('hr.deductions.index')->with('success', 'تم حذف الخصم المالي بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
